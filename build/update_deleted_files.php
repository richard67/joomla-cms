<?php
/**
 * This file is used to update the lists of deleted files, deleted folders and
 * renamed files.
 *
 * @package    Joomla.Build
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

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

// Change the following value to false when the previous major release has reached end of development.
const PREVIOUS_CHECK = true;

const PREVIOUS_VERSION = '3.10';

const PREVIOUS_BRANCH = '3.10-dev';

const GITHUB_REPO = 'https://github.com/joomla/joomla-cms.git';

function usage($command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '[options]:' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--prevBranch=<branch>:' . PHP_TAB . 'The git branch to build the previous major version from' . (PREVIOUS_BRANCH ? ', defaults to ' . PREVIOUS_BRANCH : '') . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--prevRemote=<remote>:' . PHP_TAB . 'The git remote reference to build the previous major version from, defaults to the most recent tag for the "prevBranch" branch' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--prevZipUrl=<URL>:' . PHP_TAB . 'Full package zip download URL for the previous major version' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--currRemote=<remote>:' . PHP_TAB . 'The git remote reference to build the current major version from, defaults to the most recent tag for the current branch' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--currZipUrl=<URL>:' . PHP_TAB . 'Full package zip download URL for the current major version' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--relZipUrl=<URL>:' . PHP_TAB . 'Full package zip download URL for the latest release of the current major version' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--init:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Start with empty lists' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--reuse:' . PHP_TAB . PHP_TAB . 'Reuse full package zip files from previous builds or downloads if they are present' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--temp:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Changes will not be written to the source PHP files but to their copies in the "build/tmp" folder' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--test:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Test mode, changes will be reported but not applied' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '--help:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Show this help output' . PHP_EOL;
	echo PHP_EOL;
}

$options = getopt('', ['init', 'temp', 'test', 'help', 'prevBranch::', 'prevRemote::', 'prevZipUrl::', 'currRemote::', 'currZipUrl::', 'relZipUrl::', 'reuse::']);

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

$packagesPath = __DIR__ . '/tmp/update_deleted_files/packages';
@mkdir($packagesPath, 0755, true);

// Build current major version if there is no result present from a previous build or download from URL
$currentVersionPackage = '';
$currentMajorDownload = $options['currZipUrl'] ?? '';

if (empty($currentMajorDownload))
{
	// No download URL: Check if there is a saved package from a previous build.
	$files = isset($options['reuse']) ? glob(__DIR__ . '/tmp/packages/*Full_Package.zip') : false;

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

		// Create the packages folder again because it has been deleted by the build
		@mkdir($packagesPath, 0755, true);
	}
}
else
{
	// Use download URL: Check if there is a saved package from a previous download.
	$currentVersionPackage = $packagesPath . '/' . basename($currentMajorDownload);

	if (!isset($options['reuse']) || !is_file($currentVersionPackage))
	{
		// Donwload package.
		echo PHP_EOL;
		echo 'Downloading package "' . $currentMajorDownload . '".' . PHP_EOL;

		system('curl -L -o ' . $currentVersionPackage . ' ' . $currentMajorDownload);
	}

	if (!is_file($currentVersionPackage))
	{
		$currentVersionPackage = '';
	}
}

if (!$currentVersionPackage)
{
	echo PHP_EOL;
	echo 'Error: Could not find current version package' . __DIR__ . '/tmp/packages/*Full_Package.zip.' . PHP_EOL;

	exit(1);
}

// Get the version of the current major release package
$currentVersionBuild = '';

$zipArchive = new ZipArchive();

if ($zipArchive->open($currentVersionPackage) !== true)
{
	echo PHP_EOL;
	echo 'Could not open zip archive "' . $currentVersionPackage . '".' . PHP_EOL;

	exit(1);
}

if (($xmlFileContent = $zipArchive->getFromName('administrator/manifests/files/joomla.xml')) !== false)
{
	$xml = simplexml_load_string($xmlFileContent);

	if ($xml instanceof \SimpleXMLElement && isset($xml->version))
	{
		$currentVersionBuild = (string) $xml->version;
	}
}

if (!$currentVersionBuild)
{
	echo PHP_EOL;
	echo 'Error: Could not get version from manifest XML file in the current version package.' . PHP_EOL;

	exit(1);
}

if (!preg_match('/^(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/i', $currentVersionBuild, $currentVersionBuildParts))
{
	echo PHP_EOL;
	echo 'Error: Could not get version parts from manifest XML file in the current version package.' . PHP_EOL;

	exit(1);
}

$currentVersionBuild = str_replace('-dev', '', $currentVersionBuild);
$currentMinorVersion = $currentVersionBuildParts['major'] . '.' . $currentVersionBuildParts['minor'];

// Clone and build previous major version or download from URL
if (PREVIOUS_CHECK)
{
	$previousBuildPath        = __DIR__ . '/tmp/update_deleted_files/previous-build';
	$previousBuildPackagePath = __DIR__ . '/tmp/update_deleted_files/previous-package';
	$previousMajorPackage     = '';
	$previousMajorDownload    = $options['prevZipUrl'] ?? '';

	if (empty($previousMajorDownload))
	{
		// No download URL: Check if there is a saved package from a previous build.
		$files = isset($options['reuse']) ? glob($previousBuildPackagePath . '/Joomla_' . PREVIOUS_VERSION . '.*Full_Package.zip') : false;

		if ($files !== false && count($files) > 0)
		{
			// There is one matching saved package from a previous build.
			$previousMajorPackage = $files[0];
		}

		// No package found from previous build: Clone the repository and build the previous release.
		if ($previousMajorPackage === '')
		{
			system('rm -rf ' . $previousBuildPath);

			$prevMajorBranch = $options['prevBranch'] ?? PREVIOUS_BRANCH;

			echo PHP_EOL;
			echo 'Cloning branch "' . $prevMajorBranch . '" into folder "' . $previousBuildPath . '"' . PHP_EOL;
			echo PHP_EOL;

			@mkdir($previousBuildPath, 0755, true);
			@mkdir($previousBuildPackagePath, 0755, true);

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
				$previousMajorPackage = $previousBuildPackagePath . '/' . basename($files[0]);

				copy($files[0], $previousMajorPackage);
			}

			system('rm -rf ' . $previousBuildPath);
		}
	}
	else
	{
		// Use download URL: Check if there is a saved package from a previous download.
		$previousMajorPackage = $previousBuildPackagePath . '/' . basename($previousMajorDownload);

		if (!isset($options['reuse']) || !is_file($previousMajorPackage))
		{
			// Donwload package.
			echo PHP_EOL;
			echo 'Downloading package "' . $previousMajorDownload . '".' . PHP_EOL;

			system('curl -L -o ' . $previousMajorPackage . ' ' . $previousMajorDownload);
		}

		if (!is_file($previousMajorPackage))
		{
			$previousMajorPackage = '';
		}
	}

	// If nothing found for the previous major version we can't continue.
	if (!$previousMajorPackage)
	{
		echo PHP_EOL;
		echo 'Error: Could not find previous major release package "' . $packagesPath . '/Joomla_' . PREVIOUS_VERSION . '.*Full_Package.zip".' . PHP_EOL;

		exit(1);
	}
}

$previousVersionPackageUrl = '';

if (isset($options['relZipUrl']))
{
	$previousVersionPackageUrl = $options['relZipUrl'];
}
else
{
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

	// Get the latest release before current release build version
	foreach ($gitHubReleases as $gitHubRelease)
	{
		if (version_compare(substr($gitHubRelease->tag_name, 0, strlen($currentMinorVersion)), $currentMinorVersion, '=')
			&& version_compare($gitHubRelease->tag_name, $currentVersionBuild, '<'))
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
}

$previousVersionPackage = $packagesPath . '/' . basename($previousVersionPackageUrl);

// Download full zip package of latest release before current version if not done before
if (!is_file($previousVersionPackage))
{
	echo PHP_EOL;
	echo 'Downloading package "' . $previousVersionPackageUrl . '".' . PHP_EOL;

	system('curl -L -o ' . $previousVersionPackage . ' ' . $previousVersionPackageUrl);
}

if (!is_file($previousVersionPackage))
{
	echo PHP_EOL;
	echo 'Error: Could not download package.' . PHP_EOL;

	exit(1);
}

$addedFilesFile     = __DIR__ . '/added_files.txt';
$addedFoldersFile   = __DIR__ . '/added_folders.txt';
$deletedFilesFile   = __DIR__ . '/deleted_files.txt';
$deletedFoldersFile = __DIR__ . '/deleted_folders.txt';
$renamedFilesFile   = __DIR__ . '/renamed_files.txt';

if (PREVIOUS_CHECK)
{
	echo PHP_EOL;
	echo 'Comparing from ".' . substr($previousMajorPackage, strlen(__DIR__)) . '"' . PHP_EOL;
	echo '            to ".' . substr($currentVersionPackage, strlen(__DIR__)) . '".' . PHP_EOL;

	system('php ./deleted_file_check.php --from=' . $previousMajorPackage . ' --to=' . $currentVersionPackage . ' > /dev/null');

	$addedFiles       = file_exists($addedFilesFile) ? explode("\n", file_get_contents($addedFilesFile)) : [];
	$addedFolders     = file_exists($addedFoldersFile) ? explode("\n", file_get_contents($addedFoldersFile)) : [];
	$deletedFiles     = file_exists($deletedFilesFile) ? explode("\n", file_get_contents($deletedFilesFile)) : [];
	$deletedFolders   = file_exists($deletedFoldersFile) ? explode("\n", file_get_contents($deletedFoldersFile)) : [];
	$renamedFilesRows = file_exists($renamedFilesFile) ? explode("\n", file_get_contents($renamedFilesFile)) : [];
}
else
{
	$addedFiles       = [];
	$addedFolders     = [];
	$deletedFiles     = [];
	$deletedFolders   = [];
	$renamedFilesRows = [];
}

echo PHP_EOL;
echo 'Comparing from ".' . substr($previousVersionPackage, strlen(__DIR__)) . '"' . PHP_EOL;
echo '            to ".' . substr($currentVersionPackage, strlen(__DIR__)) . '".' . PHP_EOL;

system('php ./deleted_file_check.php --from=' . $previousVersionPackage . ' --to=' . $currentVersionPackage . ' > /dev/null');

$addedFiles       = array_unique(array_merge($addedFiles, file_exists($addedFilesFile) ? explode("\n", file_get_contents($addedFilesFile)) : []));
$addedFolders     = array_unique(array_merge($addedFolders, file_exists($addedFoldersFile) ? explode("\n", file_get_contents($addedFoldersFile)) : []));
$deletedFiles     = array_unique(array_merge($deletedFiles, file_exists($deletedFilesFile) ? explode("\n", file_get_contents($deletedFilesFile)) : []));
$deletedFolders   = array_unique(array_merge($deletedFolders, file_exists($deletedFoldersFile) ? explode("\n", file_get_contents($deletedFoldersFile)) : []));
$renamedFilesRows = array_unique(array_merge($renamedFilesRows, file_exists($renamedFilesFile) ? explode("\n", file_get_contents($renamedFilesFile)) : []));

asort($deletedFiles);
rsort($deletedFolders);
asort($renamedFilesRows);

$deletedFilesRowsAdd      = [];
$deletedFilesRowsRemove   = [];
$deletedFoldersRowsAdd    = [];
$deletedFoldersRowsRemove = [];
$renamedFilesRowsAdd      = [];
$renamedFilesRowsRemove   = [];

$hasChanges   = false;
$doInit       = isset($options['init']);
$useTempFiles = isset($options['temp']);

if ($useTempFiles)
{
	$deletedFilesInfoFile   = __DIR__ . '/tmp/DeletedFiles.php';
	$deletedFoldersInfoFile = __DIR__ . '/tmp/DeletedFolders.php';
	$renamedFilesInfoFile   = __DIR__ . '/tmp/RenamedFiles.php';

	if (!is_file($deletedFilesInfoFile))
	{
		copy(JPATH_BASE . '/administrator/components/com_admin/src/Script/DeletedFiles.php', $deletedFilesInfoFile);
	}

	if (!is_file($deletedFoldersInfoFile))
	{
		copy(JPATH_BASE . '/administrator/components/com_admin/src/Script/DeletedFolders.php', $deletedFoldersInfoFile);
	}

	if (!is_file($renamedFilesInfoFile))
	{
		copy(JPATH_BASE . '/administrator/components/com_admin/src/Script/RenamedFiles.php', $renamedFilesInfoFile);
	}
}
else
{
	$deletedFilesInfoFile   = JPATH_BASE . '/administrator/components/com_admin/src/Script/DeletedFiles.php';
	$deletedFoldersInfoFile = JPATH_BASE . '/administrator/components/com_admin/src/Script/DeletedFolders.php';
	$renamedFilesInfoFile   = JPATH_BASE . '/administrator/components/com_admin/src/Script/RenamedFiles.php';
}

require_once $deletedFilesInfoFile;
require_once $deletedFoldersInfoFile;
require_once $renamedFilesInfoFile;

$deletedFilesInfo   = new DeletedFiles;
$deletedFoldersInfo = new DeletedFolders;
$renamedFilesInfo   = new RenamedFiles;

if ($doInit)
{
	$deletedFilesInfo->files     = [];
	$deletedFoldersInfo->folders = [];
	$renamedFilesInfo->files     = [];
}

// Remove files from the deleted or renamed files classes which are added back by the "to" version
foreach ($addedFiles as $addedFile)
{
	$addedFile = trim(rtrim($addedFile, ','), "'");

	if (($key = array_search($addedFile, $deletedFilesInfo->files)) !== false)
	{
		$deletedFilesRowsRemove[] = "\t\t'" . $addedFile . "',\n";

		$hasChanges = true;

		continue;
	}

	// Check for files which might have been renamed only
	$matches = preg_grep('/^' . preg_quote($addedFile, '/') . ' => /', $renamedFilesInfo->files);

	if ($matches !== false)
	{
		foreach ($matches as $key => $value)
		{
			$renamedFilesRowsRemove[] = "\t\t'" . $key . "' => '" . $value . "',\n";

			$hasChanges = true;
		}
	}
}

// Remove folders from previous results which are added back by the "to" version
foreach ($addedFolders as $addedFolder)
{
	$addedFolder = trim(rtrim($addedFolder, ','), "'");

	if (($key = array_search($addedFolder, $deletedFoldersInfo->folders)) !== false)
	{
		$deletedFoldersRowsRemove[] = "\t\t'" . $addedFolder . "',\n";

		$hasChanges = true;
	}
}

// Append current results
foreach ($deletedFiles as $deletedFile)
{
	$deletedFile = trim(rtrim($deletedFile, ','), "'");

	if (($key = array_search($deletedFile, $deletedFilesInfo->files)) === false)
	{
		$deletedFilesRowsAdd[] = "\t\t'" . $deletedFile . "',\n";

		$hasChanges = true;
	}
}

foreach ($deletedFolders as $deletedFolder)
{
	$deletedFolder = trim(rtrim($deletedFolder, ','), "'");

	if (($key = array_search($deletedFolder, $deletedFoldersInfo->folders)) === false)
	{
		$deletedFoldersRowsAdd[] = "\t\t'" . $deletedFolder . "',\n";

		$hasChanges = true;
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
			$renamedFilesRowsAdd[] = "\t\t'" . $renamedFileOld . "' => '" . $renamedFileNew . "',\n";

			$hasChanges = true;
		}
	}
}

if (!$hasChanges)
{
	echo PHP_EOL;
	echo 'There have been no changes for the deleted files and folders and renamed files lists.' . PHP_EOL;

	exit(0);
}

$deletedFilesChanged   = false;
$deletedFoldersChanged = false;
$renamedFilesChanged   = false;

if (count($deletedFilesRowsRemove) > 0)
{
	$deletedFilesChanged = true;

	echo PHP_EOL;
	echo 'The following rows have to be removed from the deleted files list because the files were added back later:' . PHP_EOL;

	foreach ($deletedFilesRowsRemove as $row)
	{
		echo $row;
	}
}

if (count($deletedFoldersRowsRemove) > 0)
{
	$deletedFoldersChanged = true;

	echo PHP_EOL;
	echo 'The following rows have to be removed from the deleted folders list because the folders were added back later:' . PHP_EOL;

	foreach ($deletedFoldersRowsRemove as $row)
	{
		echo $row;
	}
}

if (count($renamedFilesRowsRemove) > 0)
{
	$renamedFilesChanged = true;

	echo PHP_EOL;
	echo 'The following rows have to be removed from the renamed files list because the files were added back later with the old name:' . PHP_EOL;

	foreach ($renamedFilesRowsRemove as $row)
	{
		echo $row;
	}
}

if (count($deletedFilesRowsAdd) > 0)
{
	$deletedFilesChanged = true;

	echo PHP_EOL;
	echo 'The following rows have to be added to the deleted files list:' . PHP_EOL;

	foreach ($deletedFilesRowsAdd as $row)
	{
		echo $row;
	}
}

if (count($deletedFoldersRowsAdd) > 0)
{
	$deletedFoldersChanged = true;

	echo PHP_EOL;
	echo 'The following rows have to be added to the deleted folders list:' . PHP_EOL;

	foreach ($deletedFoldersRowsAdd as $row)
	{
		echo $row;
	}
}

if (count($renamedFilesRowsAdd) > 0)
{
	$renamedFilesChanged = true;

	echo PHP_EOL;
	echo 'The following rows have to be added to the renamed files list:' . PHP_EOL;

	foreach ($renamedFilesRowsAdd as $row)
	{
		echo $row;
	}
}

if (isset($options['test']))
{
	echo PHP_EOL;
	echo 'Test mode: Changes are not saved.' . PHP_EOL;

	exit(0);
}

function safeRegistryFile($rowsRemove, $rowsAdd, $filePath, $version, $doInit, $tempFiles)
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

	if (preg_match('/^\tpublic\s+\$[a-z]+\s+=\s+\[$/', $line) !== 1)
	{
		echo PHP_EOL;
		echo 'Could not find entry point for modification of file "' . $filePath . '".' . PHP_EOL;

		fclose($inFilePtr);

		exit(1);
	}

	if ($doInit)
	{
		$line = "\t];\n";
	}

	while (!$doInit && !feof($inFilePtr))
	{
		$line = fgets($inFilePtr);

		if ($line === "\t];\n")
		{
			break;
		}

		if (!in_array($line, $rowsRemove))
		{
			$output .= $line;
		}
	}

	if ($line !== "\t];\n")
	{
		echo PHP_EOL;
		echo 'Could not find starting point for appending new values to file "' . $filePath . '".' . PHP_EOL;

		fclose($inFilePtr);

		exit(1);
	}

	fclose($inFilePtr);

	$output .= "\t\t// " . $version . "\n";

	foreach ($rowsAdd as $row)
	{
		$output .= $row;
	}

	$output .= "\t];\n}\n";

	$outputFilePath = $tempFiles ? __DIR__ . '/tmp/' . basename($filePath) : $filePath;

	echo PHP_EOL;
	echo 'Writing file "' . $outputFilePath . '".' . PHP_EOL;

	file_put_contents($outputFilePath, $output);
}

if ($deletedFilesChanged)
{
	safeRegistryFile($deletedFilesRowsRemove, $deletedFilesRowsAdd, $deletedFilesInfoFile, $currentVersionBuild, $doInit, $useTempFiles);
}

if ($deletedFoldersChanged)
{
	safeRegistryFile($deletedFoldersRowsRemove, $deletedFoldersRowsAdd, $deletedFoldersInfoFile, $currentVersionBuild, $doInit, $useTempFiles);
}

if ($renamedFilesChanged)
{
	safeRegistryFile($renamedFilesRowsRemove, $renamedFilesRowsAdd, $renamedFilesInfoFile, $currentVersionBuild, $doInit, $useTempFiles);
}
