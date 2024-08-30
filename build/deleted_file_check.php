<?php

/**
 * This file is used to build the lists of deleted files, deleted folders and
 * renamed files between two Joomla versions.
 *
 * This script requires one parameter:
 *
 * --from - Full package zip file or folder with unpacked full package of the
 *          starting point for the comparison, i.e. the older version.
 *
 * This script has one additional optional parameter:
 *
 * --to - Full package zip file or folder with unpacked full package of the
 *        ending point for the comparison, i.e. the newer version.
 *
 * If the "to" parameter is not given, the full package zip from a previous
 * run of the build script is used, if present.
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
    echo PHP_EOL;
    echo '<path> can be either of the following:' . PHP_EOL;
    echo PHP_TAB . '- Path to a full package Zip file.' . PHP_EOL;
    echo PHP_TAB . '- Path to a directory where a full package Zip file has been extracted to.' . PHP_EOL;
    echo PHP_EOL;
    echo 'If the "to" parameter is not specified, file "build/tmp/packages/*Full_Package.zip"' . PHP_EOL;
    echo 'is used if it exists from a previous run of the build script.' . PHP_EOL;
    echo PHP_EOL;
}

/*
 * This is where the magic happens
 */

$options = getopt('', ['from:', 'to::']);

// We need the "from" parameter, otherwise we're doomed to fail
if (empty($options['from'])) {
    echo PHP_EOL;
    echo 'Missing "from" parameter' . PHP_EOL;

    usage($argv[0]);

    exit(1);
}

// If the "to" parameter is not specified, use the default
if (empty($options['to'])) {
    // Import the version class to get the version information
    \define('JPATH_PLATFORM', 1);
    require_once \dirname(__DIR__) . '/libraries/src/Version.php';

    $fullVersion      = (new Version())->getShortVersion();
    $packageStability = str_replace(' ', '_', Version::DEV_STATUS);
    $packageFile      = __DIR__ . '/tmp/packages/Joomla_' . $fullVersion . '-' . $packageStability . '-Full_Package.zip';

    if (is_file($packageFile)) {
        $options['to'] = $packageFile;
    } else {
        echo PHP_EOL;
        echo 'Missing "to" parameter and no zip file "' . $packageFile . '" found.' . PHP_EOL;

        usage($argv[0]);

        exit(1);
    }
}

// Check from and to if folder or zip file
if (!is_dir($options['from']) && !(is_file($options['from']) && substr(strtolower($options['from']), -4) === '.zip')) {
    echo PHP_EOL;
    echo 'The "from" parameter is neither a directory nor a zip file' . PHP_EOL;

    exit(1);
}

if (!is_dir($options['to']) && !(is_file($options['to']) && substr(strtolower($options['to']), -4) === '.zip')) {
    echo PHP_EOL;
    echo 'The "to" parameter is neither a directory nor a zip file' . PHP_EOL;

    exit(1);
}

// Directories to skip for the check
$excludedFolders = [
    'administrator/components/com_search',
    'components/com_search',
    'images/sampledata',
    'installation',
    'media/plg_quickicon_eos310',
    'media/system/images',
    'modules/mod_search',
    'plugins/captcha/recaptcha',
    'plugins/captcha/recaptcha_invisible',
    'plugins/fields/repeatable',
    'plugins/quickicon/eos310',
    'plugins/search',
    'plugins/system/compat',
    'plugins/system/logrotation',
    'plugins/system/sessiongc',
    'plugins/system/updatenotification',
    'plugins/task/demotasks',
];

/**
 * @param   string  $folderPath      Path to the folder with the extracted full package
 * @param   array   $excludeFolders  Excluded folders
 *
 * @return  stdClass  An object with arrays "files" and "folders"
 */
function readFolder($folderPath, $excludeFolders): stdClass
{
    $return = new stdClass();

    $return->files   = [];
    $return->folders = [];

    $skipFolders = [];

    foreach ($excludeFolders as $excludeFolder) {
        $skipFolders[] = $folderPath . '/' . $excludeFolder;
    }

    /**
     * @param   SplFileInfo                      $file      The file being checked
     * @param   mixed                            $key       ?
     * @param   RecursiveCallbackFilterIterator  $iterator  The iterator being processed
     *
     * @return  bool  True if you need to recurse or if the item is acceptable
     */
    $releaseFilter = function ($file, $key, $iterator) use ($skipFolders) {
        if ($iterator->hasChildren() && !\in_array($file->getPathname(), $skipFolders)) {
            return true;
        }

        return $file->isFile();
    };

    $releaseDirIterator = new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS);
    $releaseIterator    = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator($releaseDirIterator, $releaseFilter),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($releaseIterator as $info) {
        if ($info->isDir()) {
            $return->folders[] = "'" . str_replace($folderPath, '', $info->getPathname()) . "',";
            continue;
        }

        $return->files[] = "'" . str_replace($folderPath, '', $info->getPathname()) . "',";
    }

    return $return;
}

/**
 * @param   string  $filePath        Path to the full package zip file
 * @param   array   $excludeFolders  Excluded folders
 *
 * @return  stdClass  An object with arrays "files" and "folders"
 */
function readZipFile($filePath, $excludeFolders): stdClass
{
    $return = new stdClass();

    $return->files   = [];
    $return->folders = [];

    $zipArchive = new ZipArchive();

    if ($zipArchive->open($filePath) !== true) {
        echo PHP_EOL;
        echo 'Could not open zip archive "' . $filePath . '".' . PHP_EOL;

        exit(1);
    }

    $excludeRegexp = '/^(';

    foreach ($excludeFolders as $excludeFolder) {
        $excludeRegexp .= preg_quote($excludeFolder, '/') . '|';
    }

    $excludeRegexp = rtrim($excludeRegexp, '|') . ')\/.*/';

    for ($i = 0; $i < $zipArchive->numFiles; $i++) {
        $stat = $zipArchive->statIndex($i);

        $name = $stat['name'];

        if (preg_match($excludeRegexp, $name) === 1) {
            continue;
        }

        if (substr($name, -1) === '/') {
            $return->folders[] = "'/" . rtrim($name, '/') . "',";
        } else {
            $return->files[] = "'/" . $name . "',";
        }
    }

    $zipArchive->close();

    return $return;
}

// Read files and folders lists from folders or zip files
if (is_dir($options['from'])) {
    $previousReleaseFilesFolders = readFolder($options['from'], $excludedFolders);
} else {
    $previousReleaseFilesFolders = readZipFile($options['from'], $excludedFolders);
}

if (is_dir($options['to'])) {
    $newReleaseFilesFolders = readFolder($options['to'], $excludedFolders);
} else {
    $newReleaseFilesFolders = readZipFile($options['to'], $excludedFolders);
}

$filesDifferenceAdd      = array_diff($newReleaseFilesFolders->files, $previousReleaseFilesFolders->files);
$filesDifferenceDelete   = array_diff($previousReleaseFilesFolders->files, $newReleaseFilesFolders->files);
$foldersDifferenceAdd    = array_diff($newReleaseFilesFolders->folders, $previousReleaseFilesFolders->folders);
$foldersDifferenceDelete = array_diff($previousReleaseFilesFolders->folders, $newReleaseFilesFolders->folders);

// Specific files (e.g. language files) that we want to keep on upgrade
$filesToKeep = [
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
    "'/administrator/language/en-GB/plg_captcha_recaptcha.ini',",
    "'/administrator/language/en-GB/plg_captcha_recaptcha.sys.ini',",
    "'/administrator/language/en-GB/plg_captcha_recaptcha_invisible.ini',",
    "'/administrator/language/en-GB/plg_captcha_recaptcha_invisible.sys.ini',",
    "'/administrator/language/en-GB/plg_system_compat.ini',",
    "'/administrator/language/en-GB/plg_system_compat.sys.ini',",
    "'/administrator/language/en-GB/plg_system_logrotation.ini',",
    "'/administrator/language/en-GB/plg_system_logrotation.sys.ini',",
    "'/administrator/language/en-GB/plg_system_sessiongc.ini',",
    "'/administrator/language/en-GB/plg_system_sessiongc.sys.ini',",
    "'/administrator/language/en-GB/plg_system_updatenotification.ini',",
    "'/administrator/language/en-GB/plg_system_updatenotification.sys.ini',",
    "'/administrator/language/en-GB/plg_task_demotasks.ini',",
    "'/administrator/language/en-GB/plg_task_demotasks.sys.ini',",
    "'/language/en-GB/en-GB.com_search.ini',",
    "'/language/en-GB/en-GB.mod_search.ini',",
    "'/language/en-GB/en-GB.mod_search.sys.ini',",
];

// Specific folders that we want to keep on upgrade
$foldersToKeep = [
    "'/bin',",
];

// Remove folders from the results which we want to keep on upgrade
foreach ($foldersToKeep as $folder) {
    if (($key = array_search($folder, $foldersDifferenceDelete)) !== false) {
        unset($foldersDifferenceDelete[$key]);
    }
}

asort($filesDifferenceDelete);
rsort($foldersDifferenceDelete);

$deletedFiles = [];
$renamedFiles = [];

foreach ($filesDifferenceDelete as $file) {
    // Don't remove any specific files (e.g. language files) that we want to keep on upgrade
    if (array_search($file, $filesToKeep) !== false) {
        continue;
    }

    // Check for files which might have been renamed only
    $matches = preg_grep('/^' . preg_quote($file, '/') . '$/i', $newReleaseFilesFolders->files);

    if ($matches !== false) {
        foreach ($matches as $match) {
            if (\dirname($match) === \dirname($file) && strtolower(basename($match)) === strtolower(basename($file))) {
                // File has been renamed only: Add to renamed files list
                $renamedFiles[] = substr($file, 0, -1) . ' => ' . $match;

                // Go on with the next file in $filesDifferenceDelete
                continue 2;
            }
        }
    }

    // File has been really deleted and not just renamed
    $deletedFiles[] = $file;
}

// Write the lists to files for later reference
$addedFilesFile     = __DIR__ . '/added_files.txt';
$addedFoldersFile   = __DIR__ . '/added_folders.txt';
$deletedFilesFile   = __DIR__ . '/deleted_files.txt';
$deletedFoldersFile = __DIR__ . '/deleted_folders.txt';
$renamedFilesFile   = __DIR__ . '/renamed_files.txt';

@unlink($addedFilesFile);
@unlink($addedFoldersFile);
@unlink($deletedFilesFile);
@unlink($deletedFoldersFile);
@unlink($renamedFilesFile);

if (\count($filesDifferenceAdd) > 0) {
    file_put_contents($addedFilesFile, implode("\n", $filesDifferenceAdd));
}

if (\count($foldersDifferenceAdd) > 0) {
    file_put_contents($addedFoldersFile, implode("\n", $foldersDifferenceAdd));
}

if (\count($deletedFiles) > 0) {
    file_put_contents($deletedFilesFile, implode("\n", $deletedFiles));
}

if (\count($foldersDifferenceDelete) > 0) {
    file_put_contents($deletedFoldersFile, implode("\n", $foldersDifferenceDelete));
}

if (\count($renamedFiles) > 0) {
    file_put_contents($renamedFilesFile, implode("\n", $renamedFiles));
}

echo PHP_EOL;
echo 'There are ' . PHP_EOL;
echo ' - ' . \count($filesDifferenceAdd) . ' added files, ' . PHP_EOL;
echo ' - ' . \count($foldersDifferenceAdd) . ' added folders, ' . PHP_EOL;
echo ' - ' . \count($deletedFiles) . ' deleted files, ' . PHP_EOL;
echo ' - ' . \count($foldersDifferenceDelete) .  ' deleted folders and ' . PHP_EOL;
echo ' - ' . \count($renamedFiles) .  ' renamed files' . PHP_EOL;
echo PHP_EOL;
echo 'in comparison' . PHP_EOL;
echo ' from "' . $options['from'] . '"' . PHP_EOL;
echo ' to "' . $options['to'] . '"' . PHP_EOL;
echo PHP_EOL;
echo 'The following folders and their subfolders have been skipped so they were not included in the comparison:' . PHP_EOL;

foreach ($excludedFolders as $excludedFolder) {
    echo ' - ' . $excludedFolder . PHP_EOL;
}

echo PHP_EOL;
