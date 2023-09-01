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
        '/media/vendor/tinymce/plugins/template',
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
        '/libraries/vendor/symfony/polyfill-php81/Resources/stubs',
        '/libraries/vendor/symfony/polyfill-php81/Resources',
        '/libraries/vendor/symfony/polyfill-php81',
        '/libraries/vendor/symfony/polyfill-php80/Resources/stubs',
        '/libraries/vendor/symfony/polyfill-php80/Resources',
        '/libraries/vendor/symfony/polyfill-php80',
        '/libraries/vendor/symfony/polyfill-php73/Resources/stubs',
        '/libraries/vendor/symfony/polyfill-php73/Resources',
        '/libraries/vendor/symfony/polyfill-php73',
        '/libraries/vendor/symfony/polyfill-php72',
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
        // From 5.0.0-alpha2 to 5.0.0-alpha3
        '/plugins/editors/codemirror/src/Field',
        '/media/vendor/codemirror/theme',
        '/media/vendor/codemirror/mode/z80',
        '/media/vendor/codemirror/mode/yaml-frontmatter',
        '/media/vendor/codemirror/mode/yaml',
        '/media/vendor/codemirror/mode/yacas',
        '/media/vendor/codemirror/mode/xquery',
        '/media/vendor/codemirror/mode/xml',
        '/media/vendor/codemirror/mode/webidl',
        '/media/vendor/codemirror/mode/wast',
        '/media/vendor/codemirror/mode/vue',
        '/media/vendor/codemirror/mode/vhdl',
        '/media/vendor/codemirror/mode/verilog',
        '/media/vendor/codemirror/mode/velocity',
        '/media/vendor/codemirror/mode/vbscript',
        '/media/vendor/codemirror/mode/vb',
        '/media/vendor/codemirror/mode/twig',
        '/media/vendor/codemirror/mode/turtle',
        '/media/vendor/codemirror/mode/ttcn-cfg',
        '/media/vendor/codemirror/mode/ttcn',
        '/media/vendor/codemirror/mode/troff',
        '/media/vendor/codemirror/mode/tornado',
        '/media/vendor/codemirror/mode/toml',
        '/media/vendor/codemirror/mode/tiki',
        '/media/vendor/codemirror/mode/tiddlywiki',
        '/media/vendor/codemirror/mode/textile',
        '/media/vendor/codemirror/mode/tcl',
        '/media/vendor/codemirror/mode/swift',
        '/media/vendor/codemirror/mode/stylus',
        '/media/vendor/codemirror/mode/stex',
        '/media/vendor/codemirror/mode/sql',
        '/media/vendor/codemirror/mode/spreadsheet',
        '/media/vendor/codemirror/mode/sparql',
        '/media/vendor/codemirror/mode/soy',
        '/media/vendor/codemirror/mode/solr',
        '/media/vendor/codemirror/mode/smarty',
        '/media/vendor/codemirror/mode/smalltalk',
        '/media/vendor/codemirror/mode/slim',
        '/media/vendor/codemirror/mode/sieve',
        '/media/vendor/codemirror/mode/shell',
        '/media/vendor/codemirror/mode/scheme',
        '/media/vendor/codemirror/mode/sass',
        '/media/vendor/codemirror/mode/sas',
        '/media/vendor/codemirror/mode/rust',
        '/media/vendor/codemirror/mode/ruby',
        '/media/vendor/codemirror/mode/rst',
        '/media/vendor/codemirror/mode/rpm/changes',
        '/media/vendor/codemirror/mode/rpm',
        '/media/vendor/codemirror/mode/r',
        '/media/vendor/codemirror/mode/q',
        '/media/vendor/codemirror/mode/python',
        '/media/vendor/codemirror/mode/puppet',
        '/media/vendor/codemirror/mode/pug',
        '/media/vendor/codemirror/mode/protobuf',
        '/media/vendor/codemirror/mode/properties',
        '/media/vendor/codemirror/mode/powershell',
        '/media/vendor/codemirror/mode/pig',
        '/media/vendor/codemirror/mode/php',
        '/media/vendor/codemirror/mode/perl',
        '/media/vendor/codemirror/mode/pegjs',
        '/media/vendor/codemirror/mode/pascal',
        '/media/vendor/codemirror/mode/oz',
        '/media/vendor/codemirror/mode/octave',
        '/media/vendor/codemirror/mode/ntriples',
        '/media/vendor/codemirror/mode/nsis',
        '/media/vendor/codemirror/mode/nginx',
        '/media/vendor/codemirror/mode/mumps',
        '/media/vendor/codemirror/mode/mscgen',
        '/media/vendor/codemirror/mode/modelica',
        '/media/vendor/codemirror/mode/mllike',
        '/media/vendor/codemirror/mode/mirc',
        '/media/vendor/codemirror/mode/mbox',
        '/media/vendor/codemirror/mode/mathematica',
        '/media/vendor/codemirror/mode/markdown',
        '/media/vendor/codemirror/mode/lua',
        '/media/vendor/codemirror/mode/livescript',
        '/media/vendor/codemirror/mode/julia',
        '/media/vendor/codemirror/mode/jsx',
        '/media/vendor/codemirror/mode/jinja2',
        '/media/vendor/codemirror/mode/javascript',
        '/media/vendor/codemirror/mode/idl',
        '/media/vendor/codemirror/mode/http',
        '/media/vendor/codemirror/mode/htmlmixed',
        '/media/vendor/codemirror/mode/htmlembedded',
        '/media/vendor/codemirror/mode/haxe',
        '/media/vendor/codemirror/mode/haskell-literate',
        '/media/vendor/codemirror/mode/haskell',
        '/media/vendor/codemirror/mode/handlebars',
        '/media/vendor/codemirror/mode/haml',
        '/media/vendor/codemirror/mode/groovy',
        '/media/vendor/codemirror/mode/go',
        '/media/vendor/codemirror/mode/gherkin',
        '/media/vendor/codemirror/mode/gfm',
        '/media/vendor/codemirror/mode/gas',
        '/media/vendor/codemirror/mode/fortran',
        '/media/vendor/codemirror/mode/forth',
        '/media/vendor/codemirror/mode/fcl',
        '/media/vendor/codemirror/mode/factor',
        '/media/vendor/codemirror/mode/erlang',
        '/media/vendor/codemirror/mode/elm',
        '/media/vendor/codemirror/mode/eiffel',
        '/media/vendor/codemirror/mode/ecl',
        '/media/vendor/codemirror/mode/ebnf',
        '/media/vendor/codemirror/mode/dylan',
        '/media/vendor/codemirror/mode/dtd',
        '/media/vendor/codemirror/mode/dockerfile',
        '/media/vendor/codemirror/mode/django',
        '/media/vendor/codemirror/mode/diff',
        '/media/vendor/codemirror/mode/dart',
        '/media/vendor/codemirror/mode/d',
        '/media/vendor/codemirror/mode/cypher',
        '/media/vendor/codemirror/mode/css',
        '/media/vendor/codemirror/mode/crystal',
        '/media/vendor/codemirror/mode/commonlisp',
        '/media/vendor/codemirror/mode/coffeescript',
        '/media/vendor/codemirror/mode/cobol',
        '/media/vendor/codemirror/mode/cmake',
        '/media/vendor/codemirror/mode/clojure',
        '/media/vendor/codemirror/mode/clike',
        '/media/vendor/codemirror/mode/brainfuck',
        '/media/vendor/codemirror/mode/asterisk',
        '/media/vendor/codemirror/mode/asn.1',
        '/media/vendor/codemirror/mode/asciiarmor',
        '/media/vendor/codemirror/mode/apl',
        '/media/vendor/codemirror/mode',
        '/media/vendor/codemirror/lib',
        '/media/vendor/codemirror/keymap',
        '/media/vendor/codemirror/addon/wrap',
        '/media/vendor/codemirror/addon/tern',
        '/media/vendor/codemirror/addon/selection',
        '/media/vendor/codemirror/addon/search',
        '/media/vendor/codemirror/addon/scroll',
        '/media/vendor/codemirror/addon/runmode',
        '/media/vendor/codemirror/addon/mode',
        '/media/vendor/codemirror/addon/merge',
        '/media/vendor/codemirror/addon/lint',
        '/media/vendor/codemirror/addon/hint',
        '/media/vendor/codemirror/addon/fold',
        '/media/vendor/codemirror/addon/edit',
        '/media/vendor/codemirror/addon/display',
        '/media/vendor/codemirror/addon/dialog',
        '/media/vendor/codemirror/addon/comment',
        '/media/vendor/codemirror/addon',
        // From 5.0.0-alpha3 to 5.0.0-alpha4
        '/templates/system/incompatible.html,/includes',
        '/templates/system/incompatible.html,',
        '/media/plg_system_compat',
        '/media/plg_editors_tinymce/js/plugins/highlighter',
    ];
}
