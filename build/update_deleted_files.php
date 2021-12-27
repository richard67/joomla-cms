<?php
/**
 * This file is used to update the lists of deleted files, deleted folders and
 * renamed files.
 *
 * @package    Joomla.Build
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

use Joomla\CMS\Version;
use Joomla\Component\Admin\Administrator\Script\DeletedFiles;
use Joomla\Component\Admin\Administrator\Script\DeletedFolders;
use Joomla\Component\Admin\Administrator\Script\RenamedFiles;

define('JPATH_BASE', dirname(__DIR__));

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * Constants
 */
const PHP_TAB = "\t";

/*
 * As long as the previous major version is still in active development, i.e.
 * has not reached end of life, this script will by default clone the repository
 * with Git and run the build script.
 * The below two parameters define the default branch and remote to be used for
 * that.
 * How to change the default behavior the previous major version has reached end
 * of life see the comment for the constant after these two.
 */
const PREVIOUS_VERSION = '3.10';

const PREVIOUS_BRANCH = '3.10-dev';

/*
 * When the previous major version has reached end of life, change the following
 * constant to a valid download URL for the latest full installation zip package
 * of that major version. Then this script here will use that by default instead
 * of cloning the repository with Git and running the build script, and it will
 * show this default value in the help.
 */
const PREVIOUS_DOWNLOAD_URL = '';

const GITHUB_REPO = 'https://github.com/joomla/joomla-cms.git';

function usage($command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '[options]:' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--prevBranch=<branch>:' . PHP_TAB . 'The git branch to build the previous major version from, defaults to ' . PREVIOUS_BRANCH . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--prevRemote=<remote>:' . PHP_TAB . 'The git remote reference to build the previous major version from, defaults to the most recent tag for the "prevBranch" branch' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--prevZipUrl=<URL>:' . PHP_TAB . 'Full package zip download URL for the previous major version' . (PREVIOUS_DOWNLOAD_URL ? ', defaults to ' . PREVIOUS_DOWNLOAD_URL : '') . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--currRemote=<remote>:' . PHP_TAB . 'The git remote reference to build the current major version from, defaults to the most recent tag for the current branch' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--reusePackages:' . PHP_TAB . 'Reuse full package zip files from previous builds or downloads if they are present' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--test:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Test mode, changes will not be applied to the source PHP files but to copies in the "build/tmp" folder' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--help:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Show this help output' . PHP_EOL;
	echo PHP_EOL;
}

$options = getopt('', ['help', 'test', 'prevBranch::', 'prevRemote::', 'prevZipUrl::', 'currRemote::', 'reusePackages::']);

if (isset($options['help']))
{
	usage($argv[0]);

	exit(0);
}

chdir(__DIR__);

// Try to set path to git binary (e.g., /usr/local/bin/git or /usr/bin/git)
try
{
	ob_start();
	passthru('which git', $systemGit);
	$systemGit = trim(ob_get_clean());
}
catch (Exception $e)
{
	$systemGit = 'git';
}

// Build current version if there is no result present from a previous build
$currentVersionPackage = '';

$files = isset($options['reusePackages']) ? glob(__DIR__ . '/tmp/packages/*Full_Package.zip') : false;

if ($files !== false && count($files) === 1)
{
	$currentVersionPackage = $files[0];
}
else
{
	echo PHP_EOL;
	echo 'Runing build script for current version.' . PHP_EOL;
	echo PHP_EOL;

	system('php ./build.php --remote=' . ($options['currRemote'] ?? 'HEAD') . ' --exclude-gzip --exclude-bzip2');

	$files = glob(__DIR__ . '/tmp/packages/*Full_Package.zip');

	if ($files !== false && count($files) === 1)
	{
		$currentVersionPackage = $files[0];
	}
}

if (!$currentVersionPackage)
{
	echo PHP_EOL;
	echo 'Error: Could not find current version package' . __DIR__ . '/tmp/packages/*Full_Package.zip.' . PHP_EOL;

	exit(1);
}

// Clone and build previous major version or download from URL
$previousBuildPath    = __DIR__ . '/tmp/update_deleted_files/previous-build';
$previousPackagesPath = __DIR__ . '/tmp/update_deleted_files/previous-packages';

@mkdir($previousPackagesPath, 0755, true);

$previousMajorPackage  = '';
$previousMajorDownload = $options['prevZipUrl'] ?? PREVIOUS_DOWNLOAD_URL;

if (empty($previousMajorDownload))
{
	// No download URL: Check if there is a saved package from a previous build.
	$files = isset($options['reusePackages']) ? glob($previousPackagesPath . '/Joomla_' . PREVIOUS_VERSION . '.*Full_Package.zip') : false;

	if ($files !== false && count($files) > 0)
	{
		if (count($files) === 1)
		{
			// There is one matching saved package from a previous build.
			$previousMajorPackage = $files[0];
		}
		else
		{
			// There is more than one saved package from a previous build or download.
			$filesBuild = glob($previousBuildPath . '/build/tmp/packages/Joomla_' . PREVIOUS_VERSION . '.*Full_Package.zip');

			if ($filesBuild !== false && count($filesBuild) === 1)
			{
				// Check which of the saved packages belong to the previous build
				if (($key = array_search($previousPackagesPath . '/' . basename($filesBuild[0]), $files)) !== false)
				{
					$previousMajorPackage = $files[$key];
				}
			}
		}
	}

	// No package found from previous build: Clone the repository and build the previous release.
	if ($previousMajorPackage === '')
	{
		system('rm -rf ' . $previousBuildPath);

		$prevMajorBranch = $options['prevBranch'] ?? PREVIOUS_BRANCH;

		echo PHP_EOL;
		echo 'Cloning branch "' . $prevMajorBranch . '" into folder "' . $previousBuildPath . '"' . PHP_EOL;
		echo PHP_EOL;

		@mkdir($previousPackagesPath, 0755, true);
		@mkdir($previousBuildPath, 0755, true);

		chdir($previousBuildPath);

		system($systemGit . ' clone -b ' . $prevMajorBranch . ' ' . GITHUB_REPO . ' .');

		echo PHP_EOL;
		echo 'Runing build script for previous major version.' . PHP_EOL;
		echo PHP_EOL;

		system('php ./build/build.php --remote=' . ($options['prevRemote'] ?? 'HEAD') . ' --exclude-gzip --exclude-bzip2');

		chdir(__DIR__);

		$files = glob($previousBuildPath . '/build/tmp/packages/Joomla_' . PREVIOUS_VERSION . '.*Full_Package.zip');

		if ($files !== false && count($files) === 1)
		{
			$previousMajorPackage = $previousPackagesPath . '/' . basename($files[0]);

			copy($files[0], $previousMajorPackage);
		}
	}
}
else
{
	// Use download URL: Check if there is a saved package from a previous download.
	$previousMajorPackage = $previousPackagesPath . '/' . basename($previousMajorDownload);

	if (!is_file($previousMajorPackage))
	{
		// No package found: Donwload it.
		echo PHP_EOL;
		echo 'Downloading package "' . $previousMajorDownload . '".' . PHP_EOL;

		system('curl -L -o ' . $previousMajorPackage . ' ' . $previousMajorDownload);
	}
}

// If nothing found for the previous major version we can't continue.
if (!$previousMajorPackage)
{
	echo PHP_EOL;
	echo 'Error: Could not find previous major release package "' . $previousPackagesPath . '/Joomla_' . PREVIOUS_VERSION . '.*Full_Package.zip".' . PHP_EOL;

	exit(1);
}

// Fetch release information from GitHub
echo PHP_EOL;
echo 'Fetching releases information from GitHub.' . PHP_EOL;

ob_start();
passthru('curl -L https://api.github.com/repos/joomla/joomla-cms/releases', $gitHubReleasesString);
$gitHubReleasesString = trim(ob_get_clean());

if (!$gitHubReleasesString)
{
	echo PHP_EOL;
	echo 'Error: Could not get releases information from GitHub.' . PHP_EOL;

	exit(1);
}

$gitHubReleases = json_decode($gitHubReleasesString);

// Import the version class to set the version information
define('JPATH_PLATFORM', 1);
require_once JPATH_BASE . '/libraries/src/Version.php';

$currentVersion = (new Version)->getShortVersion();

$previousVersionPackageUrl = '';

// Get the latest release before current version
foreach ($gitHubReleases as $gitHubRelease)
{
	if (version_compare($gitHubRelease->tag_name, $currentVersion, '<'))
	{
		foreach ($gitHubRelease->assets as $asset)
		{
			if (preg_match('/^Joomla_.*-Full_Package\.zip$/', $asset->name) === 1)
			{
				$previousVersionPackageUrl = $asset->browser_download_url;

				break 2;
			}
		}
	}
}

if (!$previousVersionPackageUrl)
{
	echo PHP_EOL;
	echo 'Error: Could not get package download URL from GitHub.' . PHP_EOL;

	exit(1);
}

$previousVersionPackage = $previousPackagesPath . '/' . basename($previousVersionPackageUrl);

// Download full zip package of latest release before current version if not done before
if (!is_file($previousVersionPackage))
{
	echo PHP_EOL;
	echo 'Downloading package "' . $previousVersionPackageUrl . '" from GitHub.' . PHP_EOL;

	system('curl -L -o ' . $previousVersionPackage . ' ' . $previousVersionPackageUrl);
}

if (!is_file($previousVersionPackage))
{
	echo PHP_EOL;
	echo 'Error: Could not download package from GitHub.' . PHP_EOL;

	exit(1);
}

$deletedFilesInfoFile   = JPATH_BASE . '/administrator/components/com_admin/src/Script/DeletedFiles.php';
$deletedFoldersInfoFile = JPATH_BASE . '/administrator/components/com_admin/src/Script/DeletedFolders.php';
$renamedFilesInfoFile   = JPATH_BASE . '/administrator/components/com_admin/src/Script/RenamedFiles.php';

require_once $deletedFilesInfoFile;
require_once $deletedFoldersInfoFile;
require_once $renamedFilesInfoFile;

$deletedFilesInfo   = new DeletedFiles;
$deletedFoldersInfo = new DeletedFolders;
$renamedFilesInfo   = new RenamedFiles;

function compareTwoVersions($fromPackage, $toPackage, $deletedFilesInfo, $deletedFoldersInfo, $renamedFilesInfo): stdClass
{
	$return = new stdClass;

	$return->deletedFilesChanged   = false;
	$return->deletedFoldersChanged = false;
	$return->renamedFilesChanged   = false;

	$addedFilesFile     = __DIR__ . '/added_files.txt';
	$addedFoldersFile   = __DIR__ . '/added_folders.txt';
	$deletedFilesFile   = __DIR__ . '/deleted_files.txt';
	$deletedFoldersFile = __DIR__ . '/deleted_folders.txt';
	$renamedFilesFile   = __DIR__ . '/renamed_files.txt';

	echo PHP_EOL;
	echo 'Comparing from ".' . substr($fromPackage, strlen(__DIR__)) . '"' . PHP_EOL;
	echo '            to ".' . substr($toPackage, strlen(__DIR__)) . '".' . PHP_EOL;

	system('php ./deleted_file_check.php --from=' . $fromPackage . ' --to=' . $toPackage . ' > /dev/null');

	$addedFiles       = file_exists($addedFilesFile) ? explode("\n", file_get_contents($addedFilesFile)) : [];
	$addedFolders     = file_exists($addedFoldersFile) ? explode("\n", file_get_contents($addedFoldersFile)) : [];
	$deletedFiles     = file_exists($deletedFilesFile) ? explode("\n", file_get_contents($deletedFilesFile)) : [];
	$deletedFolders   = file_exists($deletedFoldersFile) ? explode("\n", file_get_contents($deletedFoldersFile)) : [];
	$renamedFilesRows = file_exists($renamedFilesFile) ? explode("\n", file_get_contents($renamedFilesFile)) : [];

	$deletedFilesAdded     = [];
	$deletedFilesRemoved   = [];
	$deletedFoldersAdded   = [];
	$deletedFoldersRemoved = [];
	$renamedFilesAdded     = [];
	$renamedFilesRemoved   = [];

	// Remove files from the deleted or renamed files classes which are added back by the "to" version
	foreach ($addedFiles as $addedFile)
	{
		$addedFile = trim(rtrim($addedFile, ','), "'");

		if (($key = array_search($addedFile, $deletedFilesInfo->files)) !== false)
		{
			$deletedFilesRemoved[] = $key;

			unset($deletedFilesInfo->files[$key]);

			$return->deletedFilesChanged = true;

			continue;
		}

		// Check for files which might have been renamed only
		$matches = preg_grep('/^' . preg_quote($addedFile, '/') . ' => /', $renamedFilesInfo->files);

		if ($matches !== false)
		{
			foreach ($matches as $key => $val)
			{
				$renamedFilesRemoved[] = $key;

				unset($renamedFilesInfo->files[$key]);

				$return->renamedFilesChanged = true;
			}
		}
	}

	// Remove folders from previous results which are added back by the "to" version
	foreach ($addedFolders as $addedFolder)
	{
		$addedFolder = trim(rtrim($addedFolder, ','), "'");

		if (($key = array_search($addedFolder, $deletedFoldersInfo->folders)) !== false)
		{
			$deletedFoldersRemoved[] = $key;

			unset($deletedFoldersInfo->folders[$key]);

			$return->deletedFoldersChanged = true;
		}
	}

	// Append current results
	foreach ($deletedFiles as $deletedFile)
	{
		$deletedFile = trim(rtrim($deletedFile, ','), "'");

		if (($key = array_search($deletedFile, $deletedFilesInfo->files)) === false)
		{
			$deletedFilesInfo->files[] = $deletedFile;

			$deletedFilesAdded[] = key(array_slice($deletedFilesInfo->files, -1, 1, true));

			$return->deletedFilesChanged = true;
		}
	}

	foreach ($deletedFolders as $deletedFolder)
	{
		$deletedFolder = trim(rtrim($deletedFolder, ','), "'");

		if (($key = array_search($deletedFolder, $deletedFoldersInfo->folders)) === false)
		{
			$deletedFoldersInfo->folders[] = $deletedFolder;

			$deletedFoldersAdded[] = key(array_slice($deletedFoldersInfo->folders, -1, 1, true));

			$return->deletedFoldersChanged = true;
		}
	}

	foreach ($renamedFilesRows as $renamedFilesRow)
	{
		if (($pos = strpos($renamedFilesRow, ' => ')) > 1)
		{
			$renamedFileOld = trim(substr($renamedFilesRow, 0, $pos), "'");
			$renamedFileNew = trim(rtrim(substr($renamedFilesRow, $pos + 4), ','), "'");

			if (!array_key_exists($renamedFileOld, $renamedFilesInfo->files))
			{
				$renamedFilesInfo->files[$renamedFileOld] = $renamedFileNew;

				$renamedFilesAdded[] = $renamedFileOld;

				$return->renamedFilesChanged = true;
			}
		}
	}

	if (!($return->deletedFilesChanged || $return->deletedFoldersChanged || $return->renamedFilesChanged))
	{
		echo PHP_EOL;
		echo 'There have been no changes for the deleted files and folders and renamed files lists.' . PHP_EOL;

		return $return;
	}

	if (count($deletedFilesRemoved) > 0)
	{
		echo PHP_EOL;
		echo 'The following files have been removed from the deleted files list because they were added back later:' . PHP_EOL;

		foreach ($deletedFilesRemoved as $key)
		{
			echo PHP_TAB . "'" . $deletedFilesInfo->files[$key] . "'" . PHP_EOL;
		}
	}

	if (count($deletedFoldersRemoved) > 0)
	{
		echo PHP_EOL;
		echo 'The following folders have been removed from the deleted folders list because they were added back later:' . PHP_EOL;

		foreach ($deletedFoldersRemoved as $key)
		{
			echo PHP_TAB . "'" . $deletedFoldersInfo->folders[$key] . "'" . PHP_EOL;
		}
	}

	if (count($renamedFilesRemoved) > 0)
	{
		echo PHP_EOL;
		echo 'The following files have been removed from the renamed files list because they were added back later with the old name:' . PHP_EOL;

		foreach ($renamedFilesRemoved as $key)
		{
			echo PHP_TAB . "'" . $renamedFilesInfo->files[$key] . "'" . PHP_EOL;
		}
	}

	if (count($deletedFilesAdded) > 0)
	{
		echo PHP_EOL;
		echo 'The following files have been added to the deleted files list:' . PHP_EOL;

		foreach ($deletedFilesAdded as $key)
		{
			echo PHP_TAB . "'" . $deletedFilesInfo->files[$key] . "'" . PHP_EOL;
		}
	}

	if (count($deletedFoldersAdded) > 0)
	{
		echo PHP_EOL;
		echo 'The following folders have been added to the deleted folders list:' . PHP_EOL;

		foreach ($deletedFoldersAdded as $key)
		{
			echo PHP_TAB . "'" . $deletedFoldersInfo->folders[$key] . "'" . PHP_EOL;
		}
	}

	if (count($renamedFilesAdded) > 0)
	{
		echo PHP_EOL;
		echo 'The following files have been added to the renamed files list:' . PHP_EOL;

		foreach ($renamedFilesAdded as $key)
		{
			echo PHP_TAB . "'" . $key . "' => '" . $renamedFilesInfo->files[$key] . "'" . PHP_EOL;
		}
	}

	return $return;
}

$changes = compareTwoVersions($previousMajorPackage, $currentVersionPackage, $deletedFilesInfo, $deletedFoldersInfo, $renamedFilesInfo);

$deletedFilesChanged   = $changes->deletedFilesChanged;
$deletedFoldersChanged = $changes->deletedFoldersChanged;
$renamedFilesChanged   = $changes->renamedFilesChanged;

$changes = compareTwoVersions($previousVersionPackage, $currentVersionPackage, $deletedFilesInfo, $deletedFoldersInfo, $renamedFilesInfo);

$deletedFilesChanged   = $deletedFilesChanged || $changes->deletedFilesChanged;
$deletedFoldersChanged = $deletedFoldersChanged || $changes->deletedFoldersChanged;
$renamedFilesChanged   = $renamedFilesChanged || $changes->renamedFilesChanged;

if (!($deletedFilesChanged || $deletedFoldersChanged || $renamedFilesChanged))
{
	echo PHP_EOL;
	echo 'There have been no changes for the deleted files and folders and renamed files lists.' . PHP_EOL;

	exit(0);
}

function safeRegistryFile($filesOrFoldersArray, $filePath, $testMode, $writeKeys = false)
{
	$inFilePtr = fopen($filePath, 'r');

	if (!$inFilePtr)
	{
		echo PHP_EOL;
		echo 'Could not open file "' . $filePath . '" for reading.' . PHP_EOL;

		exit(1);
	}

	$line   = '';
	$output = '';

	while (!feof($inFilePtr))
	{
		$line = fgets($inFilePtr);

		$output .= $line;

		if (preg_match('/^\tpublic\s+\$[a-z]+\s+=\s+\[$/', $line) === 1)
		{
			break;
		}
	}

	fclose($inFilePtr);

	if (preg_match('/^\tpublic\s+\$[a-z]+\s+=\s+\[$/', $line) !== 1)
	{
		echo PHP_EOL;
		echo 'Could not find entry point for modification of file "' . $filePath . '".' . PHP_EOL;

		exit(1);
	}

	if ($writeKeys)
	{
		foreach ($filesOrFoldersArray as $key => $value)
		{
			$output .= "\t\t'" . $key . "' => '" . $value . "',\n";
		}
	}
	else
	{
		foreach ($filesOrFoldersArray as $value)
		{
			$output .= "\t\t'" . $value . "',\n";
		}
	}

	$output .= "\t];\n}\n";

	$outputFilePath = $testMode ? __DIR__ . '/tmp/' . basename($filePath) : $filePath;

	echo PHP_EOL;
	echo 'Writing file "' . $outputFilePath . '".' . PHP_EOL;

	file_put_contents($outputFilePath, $output);
}

$testOnly = isset($options['test']);

if ($deletedFilesChanged)
{
	safeRegistryFile($deletedFilesInfo->files, $deletedFilesInfoFile, $testOnly);
}

if ($deletedFoldersChanged)
{
	safeRegistryFile($deletedFoldersInfo->folders, $deletedFoldersInfoFile, $testOnly);
}

if ($renamedFilesChanged)
{
	safeRegistryFile($renamedFilesInfo->files, $renamedFilesInfoFile, $testOnly, true);
}
