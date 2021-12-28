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
		// 3.10 changes
		'/libraries/src/Filesystem/Support/Stringcontroller.php' => '/libraries/src/Filesystem/Support/StringController.php',
		'/libraries/src/Form/Rule/SubFormRule.php' => '/libraries/src/Form/Rule/SubformRule.php',
		// 4.0.0
		'/media/vendor/skipto/js/skipTo.js' => '/media/vendor/skipto/js/skipto.js',
	];
}
