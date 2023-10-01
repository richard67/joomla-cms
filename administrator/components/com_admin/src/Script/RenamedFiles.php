<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Script;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Renamed files registry for the script file of Joomla CMS
 *
 * @since  __DEPLOY_VERSION__
 */
class RenamedFiles
{
    /**
     * The list of files to be renamed on CMS update
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    public $files = [
        // From 4.4 to 5.0
        '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/ED256.php' => '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/Ed256.php',
        '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/ED512.php' => '/libraries/vendor/web-auth/cose-lib/src/Algorithm/Signature/EdDSA/Ed512.php',
        // From 5.0.0-alpha3 to 5.0.0-alpha4
        '/plugins/schemaorg/blogposting/src/Extension/Blogposting.php' => '/plugins/schemaorg/blogposting/src/Extension/BlogPosting.php',
    ];
}
