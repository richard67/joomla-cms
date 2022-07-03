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
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Schema\ChangeItem\MysqlChangeItem
 *
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 *
 * @testdox     The MysqlChangeItem
 *
 * @since       __DEPLOY_VERSION__
 */
class MysqlChangeItemTest extends UnitTestCase
{
    /**
     * @testdox  can build the right query for skipped statements
     *
     * @dataProvider  dataBuildCheckQuerySkipped
     *
     * @param   array  $query  update statement to be skipped
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQuerySkipped($query)
    {
        $db = $this->createStub(MysqliDriver::class);

        $item = new MysqlChangeItem($db, '', $query);

        $this->assertEquals(null, $item->checkQuery);
        $this->assertEquals(null, $item->queryType);
        $this->assertEquals(1, $item->checkQueryExpected);
        $this->assertEquals([], $item->msgElements);
        $this->assertEquals(-1, $item->checkStatus);
    }

    /**
     * Provides constructor data for the testBuildCheckQueryCreateTable method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataBuildCheckQuerySkipped(): array
    {
        return [
            [null],
            [''],
            ['WHATEVER'],
        ];
    }

    /**
     * @testdox  can build the right query for CREATE TABLE statements
     *
     * @dataProvider  dataBuildCheckQueryCreateTable
     *
     * @param   array  $query  CREATE TABLE statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryCreateTable($query)
    {
        $db = $this->createStub(MysqliDriver::class);
        $db->method('getPrefix')->willReturn('jos_');
        $db->method('quote')->will(
            $this->returnCallback(function ($arg) {
                return "'" . $arg . "'";
            })
        );
        $db->method('quoteName')->will(
            $this->returnCallback(function ($arg) {
                return '`' . $arg . '`';
            })
        );

        $item = new MysqlChangeItem($db, '', $query);

        $this->assertEquals("SHOW TABLES LIKE 'jos_foo'", $item->checkQuery);
        $this->assertEquals('CREATE_TABLE', $item->queryType);
        $this->assertEquals(1, $item->checkQueryExpected);
        $this->assertEquals(["'jos_foo'"], $item->msgElements);
        $this->assertEquals(0, $item->checkStatus);
    }

    /**
     * Provides constructor data for the testBuildCheckQueryCreateTable method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataBuildCheckQueryCreateTable(): array
    {
        return [
            ['CREATE TABLE `#__foo` (`bar` text) ENGINE=InnoDB'],
            ['CREATE TABLE #__foo (`bar` text) ENGINE=InnoDB'],
            ['CREATE TABLE IF NOT EXISTS `#__foo` (`bar` text)'],
        ];
    }

    /**
     * @testdox  can build the right query for RENAME TABLE statements
     *
     * @dataProvider  dataBuildCheckQueryRenameTable
     *
     * @param   array  $query  RENAME TABLE statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryRenameTable($query)
    {
        $db = $this->createStub(MysqliDriver::class);
        $db->method('getPrefix')->willReturn('jos_');
        $db->method('quote')->will(
            $this->returnCallback(function ($arg) {
                return "'" . $arg . "'";
            })
        );
        $db->method('quoteName')->will(
            $this->returnCallback(function ($arg) {
                return '`' . $arg . '`';
            })
        );

        $item = new MysqlChangeItem($db, '', $query);

        $this->assertEquals("SHOW TABLES LIKE 'jos_bar'", $item->checkQuery);
        $this->assertEquals('RENAME_TABLE', $item->queryType);
        $this->assertEquals(1, $item->checkQueryExpected);
        $this->assertEquals(["'jos_bar'"], $item->msgElements);
        $this->assertEquals(0, $item->checkStatus);
    }

    /**
     * Provides constructor data for the testBuildCheckQueryRenameTable method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataBuildCheckQueryRenameTable(): array
    {
        return [
            ['RENAME TABLE `#__foo` TO `#__bar`'],
            ['RENAME TABLE #__foo TO #__bar'],
        ];
    }

    /**
     * @testdox  can build the right query
     *
     * @dataProvider  constructData
     *
     * @param   array  $options  Options array to inject
     * @param   array  $expects  Expected data values
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQuery($options, $expects)
    {
        $message = "Test '%s' for query '" . $options['query'] . "'";
        $utf8mb4 = $options['utf8mb4'] ?? true;

        if (!$utf8mb4) {
            $message .= ' without utf8mb4 support';
        }

        $message .= ' failed.';

        $db = $this->createStub(MysqliDriver::class);
        $db->method('getServerType')->willReturn('mysql');
        $db->method('getPrefix')->willReturn('jos_');
        $db->method('hasUTF8mb4Support')->willReturn($utf8mb4);
        $db->method('quote')->will(
            $this->returnCallback(function ($arg) {
                return "'" . $arg . "'";
            })
        );
        $db->method('quoteName')->will(
            $this->returnCallback(function ($arg) {
                return '`' . $arg . '`';
            })
        );

        $item = new MysqlChangeItem($db, '', $options['query']);

        $this->assertEquals($expects['checkQuery'], $item->checkQuery, sprintf($message, 'checkQuery'));
        $this->assertEquals($expects['queryType'], $item->queryType, sprintf($message, 'queryType'));
        $this->assertEquals($expects['checkQueryExpected'], $item->checkQueryExpected, sprintf($message, 'checkQueryExpected'));
        $this->assertEquals($expects['msgElements'], $item->msgElements, sprintf($message, 'msgElements'));
        $this->assertEquals($expects['checkStatus'], $item->checkStatus, sprintf($message, 'checkStatus'));
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
        ];
    }
}
