<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$adminScriptRenamedFiles = [
	// 3.10 changes
	'/libraries/src/Filesystem/Support/Stringcontroller.php' => '/libraries/src/Filesystem/Support/StringController.php',
	'/libraries/src/Form/Rule/SubFormRule.php' => '/libraries/src/Form/Rule/SubformRule.php',
	// 4.0.0
	'/media/vendor/skipto/js/skipTo.js' => '/media/vendor/skipto/js/skipto.js',
];
