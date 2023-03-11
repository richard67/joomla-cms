<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Script;

\defined('_JEXEC') or die;

/**
 * Deleted folders registry for the script file of Joomla CMS
 *
 * @since  __DEPLOY_VERSION__
 */
class DeletedFolders
{
    /**
     * The list of folders to be deleted on CMS update
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    public $folders = [
        // From 4.4 to 5.0
        '/plugins/system/webauthn/src/Hotfix',
        '/plugins/multifactorauth/webauthn/src/Hotfix',
        '/media/vendor/tinymce/themes/mobile',
        '/media/vendor/tinymce/skins/ui/oxide/fonts',
        '/media/vendor/tinymce/skins/ui/oxide-dark/fonts',
        '/media/vendor/tinymce/plugins/toc',
        '/media/vendor/tinymce/plugins/textpattern',
        '/media/vendor/tinymce/plugins/textcolor',
        '/media/vendor/tinymce/plugins/tabfocus',
        '/media/vendor/tinymce/plugins/spellchecker',
        '/media/vendor/tinymce/plugins/print',
        '/media/vendor/tinymce/plugins/paste',
        '/media/vendor/tinymce/plugins/noneditable',
        '/media/vendor/tinymce/plugins/legacyoutput',
        '/media/vendor/tinymce/plugins/imagetools',
        '/media/vendor/tinymce/plugins/hr',
        '/media/vendor/tinymce/plugins/fullpage',
        '/media/vendor/tinymce/plugins/contextmenu',
        '/media/vendor/tinymce/plugins/colorpicker',
        '/media/vendor/tinymce/plugins/bbcode',
        '/libraries/vendor/symfony/console/Tester/Constraint',
        '/libraries/vendor/symfony/console/Completion/Output',
        '/libraries/vendor/symfony/console/Completion',
        '/libraries/vendor/spomky-labs/base64url/src',
        '/libraries/vendor/spomky-labs/base64url',
        '/libraries/vendor/ramsey/uuid/src/Provider/Time',
        '/libraries/vendor/ramsey/uuid/src/Provider/Node',
        '/libraries/vendor/ramsey/uuid/src/Provider',
        '/libraries/vendor/ramsey/uuid/src/Generator',
        '/libraries/vendor/ramsey/uuid/src/Exception',
        '/libraries/vendor/ramsey/uuid/src/Converter/Time',
        '/libraries/vendor/ramsey/uuid/src/Converter/Number',
        '/libraries/vendor/ramsey/uuid/src/Converter',
        '/libraries/vendor/ramsey/uuid/src/Codec',
        '/libraries/vendor/ramsey/uuid/src/Builder',
        '/libraries/vendor/ramsey/uuid/src',
        '/libraries/vendor/ramsey/uuid',
        '/libraries/vendor/ramsey',
        '/libraries/vendor/psr/log/Psr/Log',
        '/libraries/vendor/psr/log/Psr',
        '/libraries/vendor/php-http/message-factory/src',
        '/libraries/vendor/php-http/message-factory',
        '/libraries/vendor/php-http',
        '/libraries/vendor/nyholm/psr7/src/Factory',
        '/libraries/vendor/nyholm/psr7/src',
        '/libraries/vendor/nyholm/psr7',
        '/libraries/vendor/nyholm',
        '/libraries/vendor/lcobucci/jwt/src/Parsing',
        '/libraries/vendor/lcobucci/jwt/src/Claim',
        '/libraries/vendor/lcobucci/jwt/compat',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/src',
        '/libraries/vendor/laminas/laminas-zendframework-bridge/config',
        '/libraries/vendor/laminas/laminas-zendframework-bridge',
        '/libraries/vendor/joomla/ldap/src',
        '/libraries/vendor/joomla/ldap',
        '/libraries/vendor/beberlei/assert/lib/Assert',
        '/libraries/vendor/beberlei/assert/lib',
        '/libraries/vendor/beberlei/assert',
        '/libraries/vendor/beberlei',
        '/administrator/components/com_admin/sql/others/mysql',
        '/administrator/components/com_admin/sql/others',
    ];
}
