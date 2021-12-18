<?php
/**
 * This file is used to build the lists of deleted files, deleted folders and
 * renamed files between two Joomla versions.
 *
 * This script requires two parameters:
 *
 * --from - Folder with unpacked full package of the starting point for the
 *          comparison, i.e. the older version.
 *
 * --to - Folder with unpacked full package of the ending point for the
 *        comparison, i.e. the newer version.
 *
 *
 * @package    Joomla.Build
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/*
 * Constants
 */
const PHP_TAB = "\t";

function usage($command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '--from <path>:' . PHP_TAB . 'Path to directory with unpacked full package for starting version' . PHP_EOL;
	echo PHP_TAB . '--to <path>:' . PHP_TAB . 'Path to directory with unpacked full package for ending version' . PHP_EOL;
	echo PHP_EOL;
}

/*
 * This is where the magic happens
 */

$options = getopt('', array('from:', 'to:'));

// We need the "from" path, otherwise we're doomed to fail
if (empty($options['from']))
{
	echo PHP_EOL;
	echo 'Missing starting directory' . PHP_EOL;

	usage($argv[0]);

	exit(1);
}

// We need the "to" path, otherwise we're doomed to fail
if (empty($options['to']))
{
	echo PHP_EOL;
	echo 'Missing ending directory' . PHP_EOL;

	usage($argv[0]);

	exit(1);
}

function getVersionFromManifest($xmlfile)
{
	$xml = simplexml_load_file($xmlfile);

	if (!($xml instanceof \SimpleXMLElement) || !isset($xml->version))
	{
		return '<unknown version>';
	}

	$version = (string) $xml->version;

	return $version ?: '<unknown version>';
}

// Build comment with versions from XML manifest files
$versionComment = '// From ' . getVersionFromManifest($options['from'] . '/administrator/manifests/files/joomla.xml')
	. ' to ' . getVersionFromManifest($options['to'] . '/administrator/manifests/files/joomla.xml') . "\n";

// Define the result files
$deletedFilesFile   = __DIR__ . '/deleted_files.txt';
$deletedFoldersFile = __DIR__ . '/deleted_folders.txt';
$renamedFilesFile   = __DIR__ . '/renamed_files.txt';

// Get previous results if some
$previousDeletedFiles   = file_exists($deletedFilesFile) ? explode("\n", file_get_contents($deletedFilesFile)) : [];
$previousDeletedFolders = file_exists($deletedFoldersFile) ? explode("\n", file_get_contents($deletedFoldersFile)) : [];
$previousRenamedFiles   = file_exists($renamedFilesFile) ? explode("\n", file_get_contents($renamedFilesFile)) : [];

// Directories to skip for the check (needs to include anything from J3 we want to keep)
$previousReleaseExclude = [
	$options['from'] . '/administrator/components/com_search',
	$options['from'] . '/components/com_search',
	$options['from'] . '/images/sampledata',
	$options['from'] . '/installation',
	$options['from'] . '/media/plg_quickicon_eos310',
	$options['from'] . '/media/system/images',
	$options['from'] . '/modules/mod_search',
	$options['from'] . '/plugins/fields/repeatable',
	$options['from'] . '/plugins/quickicon/eos310',
	$options['from'] . '/plugins/search',
];

/**
 * @param   SplFileInfo                      $file      The file being checked
 * @param   mixed                            $key       ?
 * @param   RecursiveCallbackFilterIterator  $iterator  The iterator being processed
 *
 * @return bool True if you need to recurse or if the item is acceptable
 */
$previousReleaseFilter = function ($file, $key, $iterator) use ($previousReleaseExclude) {
	if ($iterator->hasChildren() && !in_array($file->getPathname(), $previousReleaseExclude))
	{
		return true;
	}

	return $file->isFile();
};

// Directories to skip for the check
$newReleaseExclude = [
	$options['to'] . '/installation'
];

/**
 * @param   SplFileInfo                      $file      The file being checked
 * @param   mixed                            $key       ?
 * @param   RecursiveCallbackFilterIterator  $iterator  The iterator being processed
 *
 * @return bool True if you need to recurse or if the item is acceptable
 */
$newReleaseFilter = function ($file, $key, $iterator) use ($newReleaseExclude) {
	if ($iterator->hasChildren() && !in_array($file->getPathname(), $newReleaseExclude))
	{
		return true;
	}

	return $file->isFile();
};

$previousReleaseDirIterator = new RecursiveDirectoryIterator($options['from'], RecursiveDirectoryIterator::SKIP_DOTS);
$previousReleaseIterator = new RecursiveIteratorIterator(
	new RecursiveCallbackFilterIterator($previousReleaseDirIterator, $previousReleaseFilter),
	RecursiveIteratorIterator::SELF_FIRST
);
$previousReleaseFiles = [];
$previousReleaseFolders = [];

foreach ($previousReleaseIterator as $info)
{
	if ($info->isDir())
	{
		$previousReleaseFolders[] = "'" . str_replace($options['from'], '', $info->getPathname()) . "',";
		continue;
	}

	$previousReleaseFiles[] = "'" . str_replace($options['from'], '', $info->getPathname()) . "',";
}

$newReleaseDirIterator = new RecursiveDirectoryIterator($options['to'], RecursiveDirectoryIterator::SKIP_DOTS);
$newReleaseIterator = new RecursiveIteratorIterator(
	new RecursiveCallbackFilterIterator($newReleaseDirIterator, $newReleaseFilter),
	RecursiveIteratorIterator::SELF_FIRST
);
$newReleaseFiles = [];
$newReleaseFolders = [];

foreach ($newReleaseIterator as $info)
{
	if ($info->isDir())
	{
		$newReleaseFolders[] = "'" . str_replace($options['to'], '', $info->getPathname()) . "',";
		continue;
	}

	$newReleaseFiles[] = "'" . str_replace($options['to'], '', $info->getPathname()) . "',";
}

$filesDifference   = array_diff($previousReleaseFiles, $newReleaseFiles);
$foldersDifference = array_diff($previousReleaseFolders, $newReleaseFolders);

$filesAdded   = array_diff($newReleaseFiles, $previousReleaseFiles);
$foldersAdded = array_diff($newReleaseFolders, $previousReleaseFolders);

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
	$matches = preg_grep('/^' . preg_quote($file, '/') . '$/i', $newReleaseFiles);

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
	file_put_contents($deletedFilesFile, $versionComment, FILE_APPEND);
	file_put_contents($deletedFilesFile, implode("\n", $deletedFiles) . "\n", FILE_APPEND);
}

if (!empty($foldersDifference))
{
	file_put_contents($deletedFoldersFile, $versionComment, FILE_APPEND);
	file_put_contents($deletedFoldersFile, implode("\n", $foldersDifference) . "\n", FILE_APPEND);
}

if (!empty($renamedFiles))
{
	file_put_contents($renamedFilesFile, $versionComment, FILE_APPEND);
	file_put_contents($renamedFilesFile, implode("\n", $renamedFiles) . "\n", FILE_APPEND);
}

echo PHP_EOL;
echo 'There are ' . count($deletedFiles) . ' deleted files, ' . count($foldersDifference) .  ' deleted folders and ' . count($renamedFiles) .  ' renamed files in comparison from "' . $options['from'] . '" to "' . $options['to'] . '"' . PHP_EOL;
