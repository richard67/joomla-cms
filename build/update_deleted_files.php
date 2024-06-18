<?php

/**
 * This file is used to update the lists of deleted files, deleted folders and
 * renamed files.
 *
 * @package    Joomla.Build
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

// phpcs:disable PSR1.Files.SideEffects
\define('JPATH_BASE', \dirname(__DIR__));
// phpcs:enable PSR1.Files.SideEffects

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
 * Constants
 */
const PHP_TAB = "\t";

// Change the following value to false when the previous major release has reached end of development.
const PREVIOUS_CHECK = true;

const PREVIOUS_VERSION = '5.2';

const PREVIOUS_BRANCH = '5.2-dev';

const GITHUB_REPO = 'https://github.com/joomla/joomla-cms.git';

function usage($command)
{
    echo PHP_EOL;
    echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
    echo PHP_TAB . '[options]:' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--prevBranch=<branch>:' . PHP_TAB . 'The git branch to build the previous major version from' . (PREVIOUS_CHECK ? ', defaults to ' . PREVIOUS_BRANCH : '') . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--prevRemote=<remote>:' . PHP_TAB . 'The git remote reference to build the previous major version from, defaults to the most recent tag for the "prevBranch" branch' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--prevZipUrl=<URL>:' . PHP_TAB . 'Full package zip download URL for the previous major version' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--currRemote=<remote>:' . PHP_TAB . 'The git remote reference to build the current major version from, defaults to the most recent tag for the current branch' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--currZipUrl=<URL>:' . PHP_TAB . 'Full package zip download URL for the current major version' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--relZipUrl=<URL>:' . PHP_TAB . 'Full package zip download URL for the latest release of the current major version' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--init:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Start with empty lists' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--reuse:' . PHP_TAB . PHP_TAB . 'Reuse full package zip files from previous builds or downloads if they are present' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--temp:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Changes will not be written to the script.php file but to a copy in the "build/tmp" folder' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--test:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Test mode, changes will be reported but not applied' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--help:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Show this help output' . PHP_EOL;
    echo PHP_EOL;
}

$options = getopt('', ['init', 'temp', 'test', 'help', 'prevBranch::', 'prevRemote::', 'prevZipUrl::', 'currRemote::', 'currZipUrl::', 'relZipUrl::', 'reuse::']);

if (isset($options['help'])) {
    usage($argv[0]);

    exit(0);
}

chdir(__DIR__);

// Try to set path to git binary (e.g., /usr/local/bin/git or /usr/bin/git)
try {
    ob_start();
    passthru('which git', $systemGit);
    $systemGit = trim(ob_get_clean());
} catch (Exception $e) {
    $systemGit = 'git';
}

$packagesPath = __DIR__ . '/tmp/update_deleted_files/packages';
@mkdir($packagesPath, 0755, true);

// Build current major version if there is no result present from a previous build or download from URL
$currentVersionPackage = '';
$currentMajorDownload  = $options['currZipUrl'] ?? '';

if (empty($currentMajorDownload)) {
    // No download URL: Check if there is a saved package from a previous build.
    $files = isset($options['reuse']) ? glob(__DIR__ . '/tmp/packages/*Full_Package.zip') : false;

    if ($files !== false && \count($files) === 1) {
        $currentVersionPackage = $files[0];
    } else {
        echo PHP_EOL;
        echo 'Runing build script for current version.' . PHP_EOL;
        echo PHP_EOL;

        system('php ./build.php --remote=' . ($options['currRemote'] ?? 'HEAD') . ' --exclude-gzip --exclude-zstd');

        $files = glob(__DIR__ . '/tmp/packages/*Full_Package.zip');

        if ($files !== false && \count($files) === 1) {
            $currentVersionPackage = $files[0];
        }

        // Create the packages folder again because it has been deleted by the build
        @mkdir($packagesPath, 0755, true);
    }
} else {
    // Use download URL: Check if there is a saved package from a previous download.
    $currentVersionPackage = $packagesPath . '/' . basename($currentMajorDownload);

    if (!isset($options['reuse']) || !is_file($currentVersionPackage)) {
        // Donwload package.
        echo PHP_EOL;
        echo 'Downloading package "' . $currentMajorDownload . '".' . PHP_EOL;

        system('curl -L -o ' . $currentVersionPackage . ' ' . $currentMajorDownload);
    }

    if (!is_file($currentVersionPackage)) {
        $currentVersionPackage = '';
    }
}

if (!$currentVersionPackage) {
    echo PHP_EOL;
    echo 'Error: Could not find current version package' . __DIR__ . '/tmp/packages/*Full_Package.zip.' . PHP_EOL;

    exit(1);
}

// Get the version of the current major release package
$currentVersionBuild = '';

$zipArchive = new ZipArchive();

if ($zipArchive->open($currentVersionPackage) !== true) {
    echo PHP_EOL;
    echo 'Could not open zip archive "' . $currentVersionPackage . '".' . PHP_EOL;

    exit(1);
}

if (($xmlFileContent = $zipArchive->getFromName('administrator/manifests/files/joomla.xml')) !== false) {
    $xml = simplexml_load_string($xmlFileContent);

    if ($xml instanceof \SimpleXMLElement && isset($xml->version)) {
        $currentVersionBuild = (string) $xml->version;
    }
}

if (!$currentVersionBuild) {
    echo PHP_EOL;
    echo 'Error: Could not get version from manifest XML file in the current version package.' . PHP_EOL;

    exit(1);
}

if (!preg_match('/^(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/i', $currentVersionBuild, $currentVersionBuildParts)) {
    echo PHP_EOL;
    echo 'Error: Could not get version parts from manifest XML file in the current version package.' . PHP_EOL;

    exit(1);
}

$currentVersionBuild = str_replace('-dev', '', $currentVersionBuild);

if ($currentVersionBuildParts['minor'] > 0) {
    if (version_compare($currentVersionBuild, $currentVersionBuildParts['major'] . '.' . $currentVersionBuildParts['minor'] . '.0-alpha1', '>')) {
        // There should be a previous release for that minor version.
        $previousPackageVersion = $currentVersionBuildParts['major'] . '.' . $currentVersionBuildParts['minor'];
    } else {
        // There is no previous release for that minor version: Check for previous minor version.
        $previousPackageVersion = $currentVersionBuildParts['major'] . '.' . ($currentVersionBuildParts['minor'] - 1);
    }
} elseif (version_compare($currentVersionBuild, $currentVersionBuildParts['major'] . '.0.0-alpha1', '>')) {
    // There should be a previous release for minor version zero.
    $previousPackageVersion = $currentVersionBuildParts['major'] . '.' . $currentVersionBuildParts['minor'];
} else {
    // There is no previous release package for this major version.
    $previousPackageVersion = false;
}

if (!$previousPackageVersion && isset($options['relZipUrl'])) {
    echo PHP_EOL;
    echo 'There cannot be a previous release package for the build version"' . $currentVersionBuild . '". The "relZipUrl" parameter will be ignored.' . PHP_EOL;
    unset($options['relZipUrl']);
}

// Clone and build previous major version or download from URL
if (PREVIOUS_CHECK) {
    $previousBuildPath        = __DIR__ . '/tmp/update_deleted_files/previous-build';
    $previousBuildPackagePath = __DIR__ . '/tmp/update_deleted_files/previous-package';
    $previousMajorPackage     = '';
    $previousMajorDownload    = $options['prevZipUrl'] ?? '';

    if (empty($previousMajorDownload)) {
        // No download URL: Check if there is a saved package from a previous build.
        $files = isset($options['reuse']) ? glob($previousBuildPackagePath . '/Joomla_' . PREVIOUS_VERSION . '.*Full_Package.zip') : false;

        if ($files !== false && \count($files) > 0) {
            // There is one matching saved package from a previous build.
            $previousMajorPackage = $files[0];
        }

        // No package found from previous build: Clone the repository and build the previous release.
        if ($previousMajorPackage === '') {
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

            system('php ./build/build.php --remote=' . ($options['prevRemote'] ?? 'HEAD') . ' --exclude-gzip --exclude-zstd');

            chdir(__DIR__);

            $files = glob($previousBuildPath . '/build/tmp/packages/Joomla_' . PREVIOUS_VERSION . '.*Full_Package.zip');

            if ($files !== false && \count($files) === 1) {
                $previousMajorPackage = $previousBuildPackagePath . '/' . basename($files[0]);

                copy($files[0], $previousMajorPackage);
            }

            system('rm -rf ' . $previousBuildPath);
        }
    } else {
        // Use download URL: Check if there is a saved package from a previous download.
        $previousMajorPackage = $previousBuildPackagePath . '/' . basename($previousMajorDownload);

        if (!isset($options['reuse']) || !is_file($previousMajorPackage)) {
            // Donwload package.
            echo PHP_EOL;
            echo 'Downloading package "' . $previousMajorDownload . '".' . PHP_EOL;

            system('curl -L -o ' . $previousMajorPackage . ' ' . $previousMajorDownload);
        }

        if (!is_file($previousMajorPackage)) {
            $previousMajorPackage = '';
        }
    }

    // If nothing found for the previous major version we can't continue.
    if (!$previousMajorPackage) {
        echo PHP_EOL;
        echo 'Error: Could not find previous major release package "' . $packagesPath . '/Joomla_' . PREVIOUS_VERSION . '.*Full_Package.zip".' . PHP_EOL;

        exit(1);
    }
}

$previousVersionPackageUrl = '';

if (isset($options['relZipUrl'])) {
    $previousVersionPackageUrl = $options['relZipUrl'];
} elseif ($previousPackageVersion) {
    // Fetch release information from GitHub
    echo PHP_EOL;
    echo 'Fetching releases information from GitHub.' . PHP_EOL;

    ob_start();
    passthru('curl -H "Accept: application/vnd.github.v3+json" -L https://api.github.com/repos/joomla/joomla-cms/releases', $gitHubReleasesString);
    $gitHubReleasesString = trim(ob_get_clean());

    if (!$gitHubReleasesString) {
        echo PHP_EOL;
        echo 'Error: Could not get releases information from GitHub.' . PHP_EOL;

        exit(1);
    }

    $gitHubReleases = json_decode($gitHubReleasesString);

    // Get the latest release before current release build version
    foreach ($gitHubReleases as $gitHubRelease) {
        if ($gitHubRelease->draft) {
            continue;
        }

        if (
            version_compare(substr($gitHubRelease->tag_name, 0, \strlen($previousPackageVersion)), $previousPackageVersion, '=')
            && version_compare($gitHubRelease->tag_name, $currentVersionBuild, '<')
        ) {
            foreach ($gitHubRelease->assets as $asset) {
                if (preg_match('/^Joomla_.*-Full_Package\.zip$/', $asset->name) === 1) {
                    $previousVersionPackageUrl = $asset->browser_download_url;

                    break 2;
                }
            }
        }
    }

    if (!$previousVersionPackageUrl) {
        echo PHP_EOL;
        echo 'Error: Could not get package download URL from GitHub.' . PHP_EOL;

        exit(1);
    }
}

if ($previousPackageVersion) {
    $previousVersionPackage = $packagesPath . '/' . basename($previousVersionPackageUrl);

    // Download full zip package of latest release before current version if not done before
    if (!is_file($previousVersionPackage)) {
        echo PHP_EOL;
        echo 'Downloading package "' . $previousVersionPackageUrl . '".' . PHP_EOL;

        system('curl -L -o ' . $previousVersionPackage . ' ' . $previousVersionPackageUrl);
    }

    if (!is_file($previousVersionPackage)) {
        echo PHP_EOL;
        echo 'Error: Could not download package.' . PHP_EOL;

        exit(1);
    }
}

$addedFilesFile     = __DIR__ . '/added_files.txt';
$addedFoldersFile   = __DIR__ . '/added_folders.txt';
$deletedFilesFile   = __DIR__ . '/deleted_files.txt';
$deletedFoldersFile = __DIR__ . '/deleted_folders.txt';
$renamedFilesFile   = __DIR__ . '/renamed_files.txt';

if (PREVIOUS_CHECK) {
    echo PHP_EOL;
    echo 'Comparing from ".' . substr($previousMajorPackage, \strlen(__DIR__)) . '"' . PHP_EOL;
    echo '            to ".' . substr($currentVersionPackage, \strlen(__DIR__)) . '".' . PHP_EOL;

    system('php ./deleted_file_check.php --from=' . $previousMajorPackage . ' --to=' . $currentVersionPackage . ' > /dev/null');

    $addedFiles       = file_exists($addedFilesFile) ? explode("\n", file_get_contents($addedFilesFile)) : [];
    $addedFolders     = file_exists($addedFoldersFile) ? explode("\n", file_get_contents($addedFoldersFile)) : [];
    $deletedFiles     = file_exists($deletedFilesFile) ? explode("\n", file_get_contents($deletedFilesFile)) : [];
    $deletedFolders   = file_exists($deletedFoldersFile) ? explode("\n", file_get_contents($deletedFoldersFile)) : [];
    $renamedFilesRows = file_exists($renamedFilesFile) ? explode("\n", file_get_contents($renamedFilesFile)) : [];
} else {
    $addedFiles       = [];
    $addedFolders     = [];
    $deletedFiles     = [];
    $deletedFolders   = [];
    $renamedFilesRows = [];
}

if ($previousPackageVersion) {
    echo PHP_EOL;
    echo 'Comparing from ".' . substr($previousVersionPackage, \strlen(__DIR__)) . '"' . PHP_EOL;
    echo '            to ".' . substr($currentVersionPackage, \strlen(__DIR__)) . '".' . PHP_EOL;

    system('php ./deleted_file_check.php --from=' . $previousVersionPackage . ' --to=' . $currentVersionPackage . ' > /dev/null');

    $addedFiles       = array_unique(array_merge($addedFiles, file_exists($addedFilesFile) ? explode("\n", file_get_contents($addedFilesFile)) : []));
    $addedFolders     = array_unique(array_merge($addedFolders, file_exists($addedFoldersFile) ? explode("\n", file_get_contents($addedFoldersFile)) : []));
    $deletedFiles     = array_unique(array_merge($deletedFiles, file_exists($deletedFilesFile) ? explode("\n", file_get_contents($deletedFilesFile)) : []));
    $deletedFolders   = array_unique(array_merge($deletedFolders, file_exists($deletedFoldersFile) ? explode("\n", file_get_contents($deletedFoldersFile)) : []));
    $renamedFilesRows = array_unique(array_merge($renamedFilesRows, file_exists($renamedFilesFile) ? explode("\n", file_get_contents($renamedFilesFile)) : []));
}

asort($deletedFiles);
rsort($deletedFolders);
asort($renamedFilesRows);

$deletedFilesRowsAdd      = [];
$deletedFilesRowsRemove   = [];
$deletedFoldersRowsAdd    = [];
$deletedFoldersRowsRemove = [];
$renamedFilesRowsAdd      = [];
$renamedFilesRowsRemove   = [];

$hasChanges  = false;
$doInit      = isset($options['init']);
$useTempFile = isset($options['temp']);

if ($useTempFile) {
    $scriptFile = __DIR__ . '/tmp/script.php';

    if (!is_file($scriptFile)) {
        copy(JPATH_BASE . '/administrator/components/com_admin/script.php', $scriptFile);
    }
} else {
    $scriptFile = JPATH_BASE . '/administrator/components/com_admin/script.php';
}

if (
    !preg_match(
        '/^(?P<preDeletedFiles>[\s\S]*?\s+public\s+function\s+deleteUnexistingFiles[\s\S]*?\$files\s*=\s*\[\n+)'
            . '(?P<indentDeletedFile>\s+)'
            . '(?P<deletedFiles>[\s\S]*?\n+)'
            . '(?P<preDeletedFolders>\s+\];[\s\S]*?\$folders\s*=\s*\[\n+)'
            . '(?P<indentDeletedFolder>\s+)'
            . '(?P<deletedFolders>[\s\S]*?\n+)'
            . '(?P<preRenamedFiles>\s+\];[\s\S]*?\s+protected\s+function\s+fixFilenameCasing[\s\S]*?\$files\s*=\s*\[\n+)'
            . '(?P<indentRenamedFile>\s+)'
            . '(?P<renamedFiles>[\s\S]*?\n+)'
            . '(?P<post>\s+\];[\s\S]*?)$/',
        file_get_contents($scriptFile),
        $matchesScriptFile
    )
) {
    echo PHP_EOL;
    echo 'Error: Could not find relevant section in script file "' . $scriptFile . '".' . PHP_EOL;

    exit(1);
}

if ($doInit) {
    $deletedFilesOld   = [];
    $deletedFoldersOld = [];
    $renamedFilesOld   = [];
} else {
    $deletedFilesOld   = array_filter(preg_replace('/^\s*/', '', explode("\n", $matchesScriptFile['deletedFiles'])));
    $deletedFoldersOld = array_filter(preg_replace('/^\s*/', '', explode("\n", $matchesScriptFile['deletedFolders'])));
    $renamedFilesOld   = array_filter(preg_replace('/^\s*/', '', explode("\n", $matchesScriptFile['renamedFiles'])));
}

// Remove files from the deleted or renamed files classes which are added back by the "to" version
foreach ($addedFiles as $addedFile) {
    if (($key = array_search($addedFile, $deletedFilesOld)) !== false) {
        $deletedFilesRowsRemove[] = $addedFile . "\n";
        unset($deletedFilesOld[$key]);

        $hasChanges = true;

        continue;
    }

    // Check for files which might have been renamed only
    $matches = preg_grep("/^'" . preg_quote($addedFile, '/') . "'\s+=>\s+'/", $renamedFilesOld);

    if ($matches !== false) {
        foreach ($matches as $key => $value) {
            $renamedFilesRowsRemove[] = $value . "\n";
            unset($renamedFilesOld[$key]);

            $hasChanges = true;
        }
    }
}

// Remove folders from previous results which are added back by the "to" version
foreach ($addedFolders as $addedFolder) {
    if (($key = array_search($addedFolder, $deletedFoldersOld)) !== false) {
        $deletedFoldersRowsRemove[] = $addedFolder . "\n";
        unset($deletedFoldersOld[$key]);

        $hasChanges = true;
    }
}

// Append current results
foreach ($deletedFiles as $deletedFile) {
    if (array_search($deletedFile, $deletedFilesOld) === false) {
        $deletedFilesRowsAdd[] = $deletedFile . "\n";

        $hasChanges = true;
    }
}

foreach ($deletedFolders as $deletedFolder) {
    if (array_search($deletedFolder, $deletedFoldersOld) === false) {
        $deletedFoldersRowsAdd[] = $deletedFolder . "\n";

        $hasChanges = true;
    }
}

foreach ($renamedFilesRows as $renamedFilesRow) {
    if (($pos = strpos($renamedFilesRow, ' => ')) > 1) {
        $renamedFileOld = trim(substr($renamedFilesRow, 0, $pos), "'");
        $renamedFileNew = trim(rtrim(substr($renamedFilesRow, $pos + 4), ','), "'");

        $matches = preg_grep("/^'" . preg_quote($renamedFileOld, '/') . "'\s+=>\s+'" . preg_quote($renamedFileNew, '/') . "',/", $renamedFilesOld);

        if ($matches === false) {
            $renamedFilesRowsAdd[] = "'" . $renamedFileOld . "' => '" . $renamedFileNew . "',\n";

            $hasChanges = true;
        }
    }
}

if (!$hasChanges) {
    echo PHP_EOL;
    echo 'There have been no changes for the deleted files and folders and renamed files lists.' . PHP_EOL;

    exit(0);
}

if (\count($deletedFilesRowsRemove) > 0) {
    echo PHP_EOL;
    echo 'The following rows have to be removed from the deleted files list because the files were added back later:' . PHP_EOL;

    foreach ($deletedFilesRowsRemove as $row) {
        echo $row;
    }
}

if (\count($deletedFoldersRowsRemove) > 0) {
    echo PHP_EOL;
    echo 'The following rows have to be removed from the deleted folders list because the folders were added back later:' . PHP_EOL;

    foreach ($deletedFoldersRowsRemove as $row) {
        echo $row;
    }
}

if (\count($renamedFilesRowsRemove) > 0) {
    echo PHP_EOL;
    echo 'The following rows have to be removed from the renamed files list because the files were added back later with the old name:' . PHP_EOL;

    foreach ($renamedFilesRowsRemove as $row) {
        echo $row;
    }
}

if (\count($deletedFilesRowsAdd) > 0) {
    echo PHP_EOL;
    echo 'The following rows have to be added to the deleted files list:' . PHP_EOL;

    foreach ($deletedFilesRowsAdd as $row) {
        echo $row;
    }
}

if (\count($deletedFoldersRowsAdd) > 0) {
    echo PHP_EOL;
    echo 'The following rows have to be added to the deleted folders list:' . PHP_EOL;

    foreach ($deletedFoldersRowsAdd as $row) {
        echo $row;
    }
}

if (\count($renamedFilesRowsAdd) > 0) {
    echo PHP_EOL;
    echo 'The following rows have to be added to the renamed files list:' . PHP_EOL;

    foreach ($renamedFilesRowsAdd as $row) {
        echo $row;
    }
}

if (isset($options['test'])) {
    echo PHP_EOL;
    echo 'Test mode: Changes are not saved.' . PHP_EOL;

    exit(0);
}

$deletedFilesOld       = preg_replace("/^('|\/\/)/", $matchesScriptFile['indentDeletedFile'] . '\1', $deletedFilesOld);
$deletedFilesRowsAdd   = preg_replace("/^'/", $matchesScriptFile['indentDeletedFile'] . "'", $deletedFilesRowsAdd);
$deletedFoldersOld     = preg_replace("/^('|\/\/)/", $matchesScriptFile['indentDeletedFolder'] . '\1', $deletedFoldersOld);
$deletedFoldersRowsAdd = preg_replace("/^'/", $matchesScriptFile['indentDeletedFolder'] . "'", $deletedFoldersRowsAdd);
$renamedFilesOld       = preg_replace("/^('|\/\/)/", $matchesScriptFile['indentRenamedFile'] . '\1', $renamedFilesOld);
$renamedFilesRowsAdd   = preg_replace("/^'/", $matchesScriptFile['indentRenamedFile'] . "'", $renamedFilesRowsAdd);

file_put_contents($scriptFile, $matchesScriptFile['preDeletedFiles']);
file_put_contents($scriptFile, implode("\n", $deletedFilesOld) . "\n", FILE_APPEND);

if (\count($deletedFilesRowsAdd) > 0) {
    file_put_contents($scriptFile, $matchesScriptFile['indentDeletedFile'] . '// ' . $currentVersionBuild . "\n", FILE_APPEND);
    file_put_contents($scriptFile, $deletedFilesRowsAdd, FILE_APPEND);
}

file_put_contents($scriptFile, $matchesScriptFile['preDeletedFolders'], FILE_APPEND);
file_put_contents($scriptFile, implode("\n", $deletedFoldersOld) . "\n", FILE_APPEND);

if (\count($deletedFoldersRowsAdd) > 0) {
    file_put_contents($scriptFile, $matchesScriptFile['indentDeletedFolder'] . '// ' . $currentVersionBuild . "\n", FILE_APPEND);
    file_put_contents($scriptFile, $deletedFoldersRowsAdd, FILE_APPEND);
}

file_put_contents($scriptFile, $matchesScriptFile['preRenamedFiles'], FILE_APPEND);
file_put_contents($scriptFile, implode("\n", $renamedFilesOld) . "\n", FILE_APPEND);

if (\count($renamedFilesRowsAdd) > 0) {
    file_put_contents($scriptFile, $matchesScriptFile['indentRenamedFile'] . '// ' . $currentVersionBuild . "\n", FILE_APPEND);
    file_put_contents($scriptFile, $renamedFilesRowsAdd, FILE_APPEND);
}

file_put_contents($scriptFile, $matchesScriptFile['post'], FILE_APPEND);
