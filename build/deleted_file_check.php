<?php
/**
 * This file is used to build the list of deleted files between two reference points.
 *
 * This script requires one parameter:
 *
 * --from - The git commit reference to use as the starting point for the comparison.
 *
 * This script has one additional optional parameter:
 *
 * --to - The git commit reference to use as the ending point for the comparison.
 *
 * The reference parameters may be any valid identifier (i.e. a branch, tag, or commit SHA)
 *
 * @package    Joomla.Build
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Version;

/*
 * Constants
 */
const PHP_TAB = "\t";

function usage($command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '--from <path>:' . PHP_TAB . 'Path to starting version' . PHP_EOL;
	echo PHP_TAB . '--to <path>:' . PHP_TAB . 'Path to ending version [optional]' . PHP_EOL;
	echo PHP_TAB . '--comment <string>:' . PHP_TAB . 'Comment to be added at the top of the result files [optional]' . PHP_EOL;
	echo PHP_EOL;
	echo '<path> can be either of the following:' . PHP_EOL;
	echo PHP_TAB . '- Path to a full package Zip file' . PHP_EOL;
	echo PHP_TAB . '- Path to a directory where a full package Zip file has been extracted to' . PHP_EOL;
	echo PHP_EOL;
}

/*
 * This is where the magic happens
 */

$options = getopt('', array('from:', 'to::', 'comment::'));

// We need the from parameter, otherwise we're doomed to fail
if (empty($options['from']))
{
	echo PHP_EOL;
	echo 'Missing "from" parameter' . PHP_EOL;

	usage($argv[0]);

	exit(1);
}

// If the "to" parameter is not specified, use the default
if (empty($options['to']))
{
	// Import the version class to set the version information
	define('JPATH_PLATFORM', 1);
	require_once dirname(__DIR__) . '/libraries/src/Version.php';

	$fullVersion      = (new Version)->getShortVersion();
	$packageStability = str_replace(' ', '_', Version::DEV_STATUS);
	$packageFile      = __DIR__ . '/tmp/packages/Joomla_' . $fullVersion . '-' . $packageStability . '-Full_Package.zip';

	if (is_file($packageFile))
	{
		$options['to'] = $packageFile;
	}
	else
	{
		echo PHP_EOL;
		echo 'Missing "to" parameter and no zip file "' . $packageFile . '" found.' . PHP_EOL;

		usage($argv[0]);

		exit(1);
	}
}

// Check from and to if folder or zip file and set base paths for exclude filter
if (is_dir($options['from']))
{
	$fromFolderPath = $options['from'] . '/';
}
elseif (is_file($options['from']) && substr(strtolower($options['from']), -4) === '.zip')
{
	$fromFolderPath = '';
}
else
{
	echo PHP_EOL;
	echo 'The "from" parameter is neither a directory nor a zip file' . PHP_EOL;

	exit(1);
}

if (is_dir($options['to']))
{
	$toFolderPath = $options['to'] . '/';
}
elseif (is_file($options['to']) && substr(strtolower($options['to']), -4) === '.zip')
{
	$toFolderPath = '';
}
else
{
	echo PHP_EOL;
	echo 'The "to" parameter is neither a directory nor a zip file' . PHP_EOL;

	exit(1);
}

function readFolder($folderPath, $excludeFolders): stdClass
{
	$return = new stdClass;

	$return->files   = [];
	$return->folders = [];

	$releaseFilter = function ($file, $key, $iterator) use ($excludeFolders) {
		if ($iterator->hasChildren() && !in_array($file->getPathname(), $excludeFolders))
		{
			return true;
		}

		return $file->isFile();
	};

	$releaseDirIterator = new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS);
	$releaseIterator = new RecursiveIteratorIterator(
		new RecursiveCallbackFilterIterator($releaseDirIterator, $releaseFilter),
		RecursiveIteratorIterator::SELF_FIRST
	);

	foreach ($releaseIterator as $info)
	{
		if ($info->isDir())
		{
			$return->folders[] = "'" . str_replace($folderPath, '', $info->getPathname()) . "',";
			continue;
		}

		$return->files[] = "'" . str_replace($folderPath, '', $info->getPathname()) . "',";
	}

	return $return;
}

function readZipFile($filePath, $excludeFolders): stdClass
{
	$return = new stdClass;

	$return->files   = [];
	$return->folders = [];

	$zipArchive = new ZipArchive();

	if ($zipArchive->open($filePath) !== true)
	{
		echo PHP_EOL;
		echo 'Could not open zip archive "' . $filePath . '".' . PHP_EOL;

		exit(1);
	}

	$excludeRegexp = '/^(';

	foreach ($excludeFolders as $excludeFolder)
	{
		$excludeRegexp .= preg_quote($excludeFolder, '/') . '|';
	}

	$excludeRegexp = rtrim($excludeRegexp, '|') . ')\/.*/';

	for ($i = 0; $i < $zipArchive->numFiles; $i++)
	{
		$stat = $zipArchive->statIndex($i);

		$name = $stat['name'];

		if (preg_match($excludeRegexp, $name) === 1)
		{
			continue;
		}

		if (substr($name, -1) === '/')
		{
			$return->folders[] = "'/" . rtrim($name, '/') . "',";
		}
		else
		{
			$return->files[] = "'/" . $name . "',";
		}
	}

	$zipArchive->close();

	return $return;
}

// Directories to skip for the check (needs to include anything from J3 we want to keep)
$previousReleaseExclude = [
	$fromFolderPath . 'administrator/components/com_search',
	$fromFolderPath . 'components/com_search',
	$fromFolderPath . 'images/sampledata',
	$fromFolderPath . 'installation',
	$fromFolderPath . 'media/plg_quickicon_eos310',
	$fromFolderPath . 'media/system/images',
	$fromFolderPath . 'modules/mod_search',
	$fromFolderPath . 'plugins/fields/repeatable',
	$fromFolderPath . 'plugins/quickicon/eos310',
	$fromFolderPath . 'plugins/search',
];

// Directories of the ending version to skip for the check
$newReleaseExclude = [
	$toFolderPath . 'installation'
];

// Read files and folders lists folders or zip files
if (is_dir($options['from']))
{
	$previousReleaseFilesFolders = readFolder($options['from'], $previousReleaseExclude);
}
else
{
	$previousReleaseFilesFolders = readZipFile($options['from'], $previousReleaseExclude);
}

if (is_dir($options['to']))
{
	$newReleaseFilesFolders = readFolder($options['to'], $newReleaseExclude);
}
else
{
	$newReleaseFilesFolders = readZipFile($options['to'], $newReleaseExclude);
}

$filesDifference   = array_diff($previousReleaseFilesFolders->files, $newReleaseFilesFolders->files);
$foldersDifference = array_diff($previousReleaseFilesFolders->folders, $newReleaseFilesFolders->folders);

$filesAdded   = array_diff($newReleaseFilesFolders->files, $previousReleaseFilesFolders->files);
$foldersAdded = array_diff($newReleaseFilesFolders->folders, $previousReleaseFilesFolders->folders);

// Define the result files
$deletedFilesFile   = __DIR__ . '/deleted_files.txt';
$deletedFoldersFile = __DIR__ . '/deleted_folders.txt';
$renamedFilesFile   = __DIR__ . '/renamed_files.txt';

// Get previous results if some
$previousDeletedFiles   = file_exists($deletedFilesFile) ? explode("\n", file_get_contents($deletedFilesFile)) : [];
$previousDeletedFolders = file_exists($deletedFoldersFile) ? explode("\n", file_get_contents($deletedFoldersFile)) : [];
$previousRenamedFiles   = file_exists($renamedFilesFile) ? explode("\n", file_get_contents($renamedFilesFile)) : [];

// Remove files from previous results which are added back by the "to" version
if (!empty($filesAdded))
{
	if (!empty($previousDeletedFiles))
	{
		$previousDeletedFiles = array_diff($previousDeletedFiles, $filesAdded);
	}

	if (!empty($previousRenamedFiles))
	{
		foreach ($filesAdded as $fileAdded)
		{
			// Check for files which might have been renamed only
			$matches = preg_grep('/^' . preg_quote($fileAdded, '/') . ' => /', $previousRenamedFiles);

			if ($matches !== false)
			{
				foreach ($matches as $key => $val)
				{
					unset($previousRenamedFiles[$key]);
				}
			}
		}
	}
}

// Remove folders from previous results which are added back by the "to" version
if (!empty($previousDeletedFolders) && !empty($foldersAdded))
{
	$previousDeletedFolders = array_diff($previousDeletedFolders, $foldersAdded);
}

// Specific files (e.g. language files) that we want to keep on upgrade
$filesToKeep = [
	"'/administrator/components/com_joomlaupdate/restore_finalisation.php',",
	"'/administrator/language/en-GB/en-GB.com_search.ini',",
	"'/administrator/language/en-GB/en-GB.com_search.sys.ini',",
	"'/administrator/language/en-GB/en-GB.plg_editors-xtd_weblink.ini',",
	"'/administrator/language/en-GB/en-GB.plg_editors-xtd_weblink.sys.ini',",
	"'/administrator/language/en-GB/en-GB.plg_fields_repeatable.ini',",
	"'/administrator/language/en-GB/en-GB.plg_fields_repeatable.sys.ini',",
	"'/administrator/language/en-GB/en-GB.plg_quickicon_eos310.ini',",
	"'/administrator/language/en-GB/en-GB.plg_quickicon_eos310.sys.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_categories.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_categories.sys.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_contacts.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_contacts.sys.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_content.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_content.sys.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_newsfeeds.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_newsfeeds.sys.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_tags.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_tags.sys.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_weblinks.ini',",
	"'/administrator/language/en-GB/en-GB.plg_search_weblinks.sys.ini',",
	"'/administrator/language/en-GB/en-GB.plg_system_weblinks.ini',",
	"'/administrator/language/en-GB/en-GB.plg_system_weblinks.sys.ini',",
	"'/language/en-GB/en-GB.com_search.ini',",
	"'/language/en-GB/en-GB.mod_search.ini',",
	"'/language/en-GB/en-GB.mod_search.sys.ini',",
];

// Specific folders that we want to keep on upgrade
$foldersToKeep = [
	"'/bin',",
];

// Remove folders from the results which we want to keep on upgrade
foreach ($foldersToKeep as $folder)
{
	if (($key = array_search($folder, $foldersDifference)) !== false) {
		unset($foldersDifference[$key]);
	}
}

// Remove folders from the results which are already present in the result file from the previous release
$foldersDifference = array_diff($foldersDifference, $previousDeletedFolders);

asort($filesDifference);
rsort($foldersDifference);

$deletedFiles = [];
$renamedFiles = [];

foreach ($filesDifference as $file)
{
	// Don't remove any specific files (e.g. language files) that we want to keep on upgrade
	if (array_search($file, $filesToKeep) !== false)
	{
		continue;
	}

	// Check for files which might have been renamed only
	$matches = preg_grep('/^' . preg_quote($file, '/') . '$/i', $newReleaseFilesFolders->files);

	if ($matches !== false)
	{
		foreach ($matches as $match)
		{
			if (dirname($match) === dirname($file) && strtolower(basename($match)) === strtolower(basename($file)))
			{
				// File has been renamed only: Add to renamed files list
				$renamedFiles[] = substr($file, 0, -1) . ' => ' . $match;

				// Go on with the next file in $filesDifference
				continue 2;
			}
		}
	}

	// File has been really deleted and not just renamed
	$deletedFiles[] = $file;
}

// Remove files from the results which are already present in the result files
$deletedFiles = array_diff($deletedFiles, $previousDeletedFiles);
$renamedFiles = array_diff($renamedFiles, $previousRenamedFiles);

// Write the lists to files for later reference
if (!empty($previousDeletedFiles))
{
	file_put_contents($deletedFilesFile, implode("\n", $previousDeletedFiles));
}

if (!empty($previousDeletedFolders))
{
	file_put_contents($deletedFoldersFile, implode("\n", $previousDeletedFolders));
}

if (!empty($previousRenamedFiles))
{
	file_put_contents($renamedFilesFile, implode("\n", $previousRenamedFiles));
}

if (!empty($deletedFiles))
{
	if (!empty($options['comment']))
	{
		file_put_contents($deletedFilesFile, '// ' . $options['comment'] . "\n", FILE_APPEND);
	}
	file_put_contents($deletedFilesFile, implode("\n", $deletedFiles) . "\n", FILE_APPEND);
}

if (!empty($foldersDifference))
{
	if (!empty($options['comment']))
	{
		file_put_contents($deletedFoldersFile, '// ' . $options['comment'] . "\n", FILE_APPEND);
	}
	file_put_contents($deletedFoldersFile, implode("\n", $foldersDifference) . "\n", FILE_APPEND);
}

if (!empty($renamedFiles))
{
	if (!empty($options['comment']))
	{
		file_put_contents($renamedFilesFile, '// ' . $options['comment'] . "\n", FILE_APPEND);
	}
	file_put_contents($renamedFilesFile, implode("\n", $renamedFiles) . "\n", FILE_APPEND);
}

echo PHP_EOL;
echo 'There are ' . count($deletedFiles) . ' deleted files, ' . count($foldersDifference) .  ' deleted folders and ' . count($renamedFiles) .  ' renamed files in comparison from "' . $options['from'] . '" to "' . $options['to'] . '"' . PHP_EOL;
