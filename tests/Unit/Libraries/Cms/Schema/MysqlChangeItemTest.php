<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Tests\Unit\Libraries\Cms\Schema;

use Joomla\CMS\Schema\ChangeItem\MysqlChangeItem;
use Joomla\Database\Mysqli\MysqliDriver;

class MysqlChangeItemTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * @var  MysqliDriver|MockObject
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Sets up the database mock.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function setUp():void
	{
		$this->db = $this->createMock(MysqliDriver::class);

		$this->db->expects($this->once())
			->method('getServerType')
			->willReturn('mysql');

		$this->db->expects($this->any())
			->method('getPrefix')
			->willReturn('jos_');

		$this->db->expects($this->any())
			->method('quote')->will(
				$this->returnCallback(function ($arg) {
					return "'" . $arg . "'";
				})
			);

		$this->db->expects($this->any())
			->method('quoteName')->will(
				$this->returnCallback(function ($arg) {
					return '`' . $arg . '`';
				})
			);
	}

	/**
	 * Provides constructor data for test methods
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function constructData(): array
	{
		return [
			[
				[
					'query' => 'WHATEVER',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => null,
					'queryType' => null,
					'checkQueryExpected' => 1,
					'msgElements' => [],
					'checkStatus' => -1,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD COLUMN `bar` text',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar'",
					'queryType' => 'ADD_COLUMN',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE #__foo ADD COLUMN bar text',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN #__foo WHERE field = 'bar'",
					'queryType' => 'ADD_COLUMN',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD `bar` text',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar'",
					'queryType' => 'ADD_COLUMN',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD INDEX `idx_bar` (`bar`)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'ADD_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD INDEX `idx_bar`(`bar`)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'ADD_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD KEY `idx_bar` (`bar`)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'ADD_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD KEY `idx_bar`(`bar`)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'ADD_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD UNIQUE `idx_bar` (`bar`)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'ADD_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD UNIQUE `idx_bar`(`bar`)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'ADD_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD UNIQUE INDEX `idx_bar` (`bar`)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'ADD_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD UNIQUE INDEX `idx_bar`(`bar`)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'ADD_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD UNIQUE KEY `idx_bar` (`bar`)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'ADD_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ADD UNIQUE KEY `idx_bar`(`bar`)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'ADD_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` ALTER COLUMN `bar` DROP DEFAULT',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND `default` IS NULL",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "NOT DEFAULT"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` ALTER COLUMN `bar` SET DEFAULT NULL",
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND `default` IS NULL",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "DEFAULT NULL"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` ALTER COLUMN `bar` SET DEFAULT ''",
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND `default` = ''",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "DEFAULT ''"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE COLUMN `bar` `bar_new` mediumtext",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) = 'MEDIUMTEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "mediumtext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE COLUMN `bar` `bar_new` mediumtext",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) IN ('MEDIUMTEXT','LONGTEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "mediumtext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE COLUMN `bar` `bar_new` text",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) = 'TEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "text"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE COLUMN `bar` `bar_new` text",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) IN ('TEXT','MEDIUMTEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "text"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE COLUMN `bar` `bar_new` tinytext",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) = 'TINYTEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "tinytext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE COLUMN `bar` `bar_new` tinytext",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) IN ('TINYTEXT','TEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "tinytext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE `bar` `bar_new` mediumtext",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) = 'MEDIUMTEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "mediumtext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE `bar` `bar_new` mediumtext",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) IN ('MEDIUMTEXT','LONGTEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "mediumtext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE `bar` `bar_new` text",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) = 'TEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "text"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE `bar` `bar_new` text",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) IN ('TEXT','MEDIUMTEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "text"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE `bar` `bar_new` tinytext",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) = 'TINYTEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "tinytext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` CHANGE `bar` `bar_new` tinytext",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) IN ('TINYTEXT','TEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'", "tinytext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` DROP COLUMN `bar`',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE Field = 'bar'",
					'queryType' => 'DROP_COLUMN',
					'checkQueryExpected' => 0,
					'msgElements' => ["'jos_foo'", "'bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` DROP `bar`',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE Field = 'bar'",
					'queryType' => 'DROP_COLUMN',
					'checkQueryExpected' => 0,
					'msgElements' => ["'jos_foo'", "'bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` DROP INDEX `idx_bar`',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'DROP_INDEX',
					'checkQueryExpected' => 0,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` DROP KEY `idx_bar`',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'",
					'queryType' => 'DROP_INDEX',
					'checkQueryExpected' => 0,
					'msgElements' => ["'jos_foo'", "'idx_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY COLUMN `bar` mediumtext",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) = 'MEDIUMTEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "mediumtext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY COLUMN `bar` mediumtext",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) IN ('MEDIUMTEXT','LONGTEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "mediumtext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY COLUMN `bar` text",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) = 'TEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "text"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY COLUMN `bar` text",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) IN ('TEXT','MEDIUMTEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "text"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY COLUMN `bar` tinytext",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) = 'TINYTEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "tinytext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY COLUMN `bar` tinytext",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) IN ('TINYTEXT','TEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "tinytext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY `bar` mediumtext",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) = 'MEDIUMTEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "mediumtext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY `bar` mediumtext",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) IN ('MEDIUMTEXT','LONGTEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "mediumtext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY `bar` text",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) = 'TEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "text"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY `bar` text",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) IN ('TEXT','MEDIUMTEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "text"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY `bar` tinytext",
					'utf8mb4' => false,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) = 'TINYTEXT'",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "tinytext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => "ALTER TABLE `#__foo` MODIFY `bar` tinytext",
					'utf8mb4' => true,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE field = 'bar' AND UPPER(type) IN ('TINYTEXT','TEXT')",
					'queryType' => 'CHANGE_COLUMN_TYPE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'", "tinytext"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` RENAME COLUMN `bar_old` TO `bar_new`',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW COLUMNS IN `#__foo` WHERE Field = 'bar_new'",
					'queryType' => 'RENAME_COLUMN',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar_new'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` RENAME INDEX `idx_bar_old` TO `idx_bar_new`',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar_new'",
					'queryType' => 'RENAME_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar_new'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` RENAME KEY `idx_bar_old` TO `idx_bar_new`',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar_new'",
					'queryType' => 'RENAME_INDEX',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'idx_bar_new'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` RENAME TO `#__bar`',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW TABLES LIKE 'jos_bar'",
					'queryType' => 'RENAME_TABLE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'ALTER TABLE `#__foo` RENAME `#__bar`',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW TABLES LIKE 'jos_bar'",
					'queryType' => 'RENAME_TABLE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'CREATE TABLE `#__foo` (`bar` text)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW TABLES LIKE 'jos_foo'",
					'queryType' => 'CREATE_TABLE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'CREATE TABLE IF NOT EXISTS `#__foo` (`bar` text)',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW TABLES LIKE 'jos_foo'",
					'queryType' => 'CREATE_TABLE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'RENAME TABLE `#__foo` TO `#__bar`',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW TABLES LIKE 'jos_bar'",
					'queryType' => 'RENAME_TABLE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_bar'"],
					'checkStatus' => 0,
				],
			],
			[
				[
					'query' => 'RENAME TABLE #__foo TO #__bar',
					'utf8mb4' => null,
				],
				[
					'checkQuery' => "SHOW TABLES LIKE 'jos_bar'",
					'queryType' => 'RENAME_TABLE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_bar'"],
					'checkStatus' => 0,
				],
			],
		];
	}

	/**
	 * @param   array  $options  Options array to inject
	 * @param   array  $expects  Expected data values
	 *
	 * @dataProvider constructData
	 *
	 * @return void
	 * @since   __DEPLOY_VERSION__
	 */
	public function testBuildCheckQuery($options, $expects)
	{
		$message = "Test '%s' for query '". $options['query'] . "'";
		$utf8mb4 = $options['utf8mb4'] ?? true;

		if (!$utf8mb4)
		{
			$message .= ' without utf8mb4 support';
		}

		$message .= ' failed.';

		$this->db->expects($this->any())
			->method('hasUTF8mb4Support')
			->willReturn($utf8mb4);

		$item = MysqlChangeItem::getInstance($this->db, '/not/really/used/4.0.0-2018-03-05.sql', $options['query']);

		$this->assertEquals($expects['checkQuery'], $item->checkQuery, sprintf($message, 'checkQuery'));
		$this->assertEquals($expects['queryType'], $item->queryType, sprintf($message, 'queryType'));
		$this->assertEquals($expects['checkQueryExpected'], $item->checkQueryExpected, sprintf($message, 'checkQueryExpected'));
		$this->assertEquals($expects['msgElements'], $item->msgElements, sprintf($message, 'msgElements'));
		$this->assertEquals($expects['checkStatus'], $item->checkStatus, sprintf($message, 'checkStatus'));
	}
}
