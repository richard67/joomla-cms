<?php

/**
 * @package        Joomla.Build
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs          :disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

echo <<< TEXT
Update FIDO Cache version 1.0

Distributed under the GNU General Public License version 2, or at your option
any later version published by the Free Software Foundation.

TEXT;

if (!isset($fullPath)) {
    $fullPath = \dirname(__DIR__);
}

if (preg_match('#(.*)/build/tmp/\d{1,10}#', $fullPath, $matches)) {
    $downloadPath = $matches[1] . '/fido.jwt';
} else {
    $downloadPath = $fullPath . '/fido.jwt';
}

$filePath = rtrim($fullPath, '\\/') . '/plugins/system/webauthn/fido.jwt';

if (is_file($filePath) && filemtime($filePath) > (time() - 864000)) {
    echo "The file $filePath already exists and is current; nothing to do.\n";

    exit(0);
}

if (is_file($downloadPath) && filemtime($downloadPath) > (time() - 864000)) {
    if (copy($downloadPath, $filePath)) {
        echo "File $filePath copied from previous download $downloadPath.\n";

        exit(0);
    }
}

echo "Fetching FIDO metadata statements...\n";

$context = stream_context_create([
    'http' => [
        'method'          => 'GET',
        'follow_location' => 1,
        'timeout'         => 5.0,
    ],
]);

$rawJwt = @file_get_contents('https://mds.fidoalliance.org/', false, $context);

if ($rawJwt === false) {
    echo "Could not get an updated fido.jwt file.\n";

    return;
}

echo "Saving JWT file...\n";

file_put_contents($downloadPath, $rawJwt);

echo "File saved: $downloadPath\n";

echo "Copy saved JWT file to plugin directory...\n";

if (!copy($downloadPath, $filePath)) {
    echo "Failed to copy file $downloadPath to $filePath.\n";

    return;
}

echo "File $downloadPath to $filePath.\n";

