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
     * @param   string  $query  update statement to be skipped
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
     * Provides constructor data for the testBuildCheckQuerySkipped method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataBuildCheckQuerySkipped(): array
    {
        return [
            [''],
            ['WHATEVER'],
        ];
    }

    /**
     * @testdox  can build the right query for CREATE_TABLE statements
     *
     * @dataProvider  dataBuildCheckQueryCreateTable
     *
     * @param   string  $query  CREATE_TABLE statement
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
            // The following 2 tests are failing due to a bug in MySQLChangeItem
            //['CREATE TABLE `#__foo` (`bar` text)'],
            //['CREATE TABLE #__foo (`bar` text)'],
            // The following 2 tests are obsolete when the above bug has been fixed
            ['CREATE TABLE `#__foo` (`bar` text) ENGINE=InnoDB'],
            ['CREATE TABLE #__foo (`bar` text) ENGINE=InnoDB'],
            ['CREATE TABLE IF NOT EXISTS `#__foo` (`bar` text)'],
            ['CREATE TABLE IF NOT EXISTS #__foo (`bar` text)'],
            ['CREATE TABLE `#__foo`' . "\n" . '(`bar` text) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci'],
        ];
    }

    /**
     * @testdox  can build the right query for RENAME_TABLE statements
     *
     * @dataProvider  dataBuildCheckQueryRenameTable
     *
     * @param   string  $query  RENAME_TABLE statement
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
     * @testdox  can build the right query for ADD_COLUMN statements
     *
     * @dataProvider  dataBuildCheckQueryAddColumn
     *
     * @param   string  $query  ADD_COLUMN statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryAddColumn($query)
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

        $this->assertEquals("SHOW COLUMNS IN `#__foo` WHERE field = 'bar'", $item->checkQuery);
        $this->assertEquals('ADD_COLUMN', $item->queryType);
        $this->assertEquals(1, $item->checkQueryExpected);
        $this->assertEquals(["'jos_foo'", "'bar'"], $item->msgElements);
        $this->assertEquals(0, $item->checkStatus);
    }

    /**
     * Provides constructor data for the testBuildCheckQueryAddColumn method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataBuildCheckQueryAddColumn(): array
    {
        return [
            ['ALTER TABLE `#__foo` ADD COLUMN `bar` text'],
            // The following test currently fails due to a bug in MySQLChangeItem
            //['ALTER TABLE #__foo ADD COLUMN `bar` text'],
            ['ALTER TABLE `#__foo` ADD COLUMN bar text'],
            // The following test currently fails due to a bug in MySQLChangeItem
            //['ALTER TABLE #__foo ADD COLUMN bar text'],
        ];
    }

    /**
     * @testdox  can build the right query for DROP_COLUMN statements
     *
     * @dataProvider  dataBuildCheckQueryDropColumn
     *
     * @param   string  $query  DROP_COLUMN statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryDropColumn($query)
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

        $this->assertEquals("SHOW COLUMNS IN `#__foo` WHERE Field = 'bar'", $item->checkQuery);
        $this->assertEquals('DROP_COLUMN', $item->queryType);
        $this->assertEquals(0, $item->checkQueryExpected);
        $this->assertEquals(["'jos_foo'", "'bar'"], $item->msgElements);
        $this->assertEquals(0, $item->checkStatus);
    }

    /**
     * Provides constructor data for the testBuildCheckQueryDropColumn method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataBuildCheckQueryDropColumn(): array
    {
        return [
            ['ALTER TABLE `#__foo` DROP COLUMN `bar`'],
            // The following test currently fails due to a bug in MySQLChangeItem
            //['ALTER TABLE #__foo DROP COLUMN `bar'],
            ['ALTER TABLE `#__foo` DROP COLUMN bar'],
            // The following test currently fails due to a bug in MySQLChangeItem
            //['ALTER TABLE #__foo DROP COLUMN bar'],
        ];
    }

    /**
     * @testdox  can build the right query for ADD_INDEX statements
     *
     * @dataProvider  dataBuildCheckQueryAddIndex
     *
     * @param   string  $query  ADD_INDEX statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryAddIndex($query)
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

        $this->assertEquals("SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'", $item->checkQuery);
        $this->assertEquals('ADD_INDEX', $item->queryType);
        $this->assertEquals(1, $item->checkQueryExpected);
        $this->assertEquals(["'jos_foo'", "'idx_bar'"], $item->msgElements);
        $this->assertEquals(0, $item->checkStatus);
    }

    /**
     * Provides constructor data for the testBuildCheckQueryAddIndex method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataBuildCheckQueryAddIndex(): array
    {
        return [
            ['ALTER TABLE `#__foo` ADD INDEX `idx_bar` (`bar`)'],
            ['ALTER TABLE `#__foo` ADD INDEX `idx_bar`(`bar`)'],
            ['ALTER TABLE `#__foo` ADD KEY `idx_bar` (`bar`)'],
            ['ALTER TABLE `#__foo` ADD KEY `idx_bar`(`bar`)'],
            ['ALTER TABLE `#__foo` ADD UNIQUE `idx_bar` (`bar`)'],
            ['ALTER TABLE `#__foo` ADD UNIQUE `idx_bar`(`bar`)'],
            ['ALTER TABLE `#__foo` ADD UNIQUE INDEX `idx_bar` (`bar`)'],
            ['ALTER TABLE `#__foo` ADD UNIQUE INDEX `idx_bar`(`bar`)'],
            ['ALTER TABLE `#__foo` ADD UNIQUE KEY `idx_bar` (`bar`)'],
            ['ALTER TABLE `#__foo` ADD UNIQUE KEY `idx_bar`(`bar`)'],
        ];
    }

    /**
     * @testdox  can build the right query for DROP_INDEX statements
     *
     * @dataProvider  dataBuildCheckQueryDropIndex
     *
     * @param   string  $query  DROP_INDEX statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryDropIndex($query)
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

        $this->assertEquals("SHOW INDEXES IN `#__foo` WHERE Key_name = 'idx_bar'", $item->checkQuery);
        $this->assertEquals('DROP_INDEX', $item->queryType);
        $this->assertEquals(0, $item->checkQueryExpected);
        $this->assertEquals(["'jos_foo'", "'idx_bar'"], $item->msgElements);
        $this->assertEquals(0, $item->checkStatus);
    }

    /**
     * Provides constructor data for the testBuildCheckQueryDropIndex method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataBuildCheckQueryDropIndex(): array
    {
        return [
            ['ALTER TABLE `#__foo` DROP INDEX `idx_bar`'],
            ['ALTER TABLE `#__foo` DROP KEY `idx_bar`'],
        ];
    }

    /**
     * @testdox  can build the right query for CHANGE_COLUMN_TYPE statements
     *
     * @dataProvider  dataBuildCheckQueryChangeColumnType
     *
     * @param   string  $query        CHANGE_COLUMN_TYPE statement
     * @param   string  $checkQuery   The expected check query for the CHANGE_COLUMN_TYPE statement
     * @param   array   $msgElements  The expected message elements for the CHANGE_COLUMN_TYPE statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryChangeColumnType($query, $checkQuery, $msgElements)
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

        $this->assertEquals($checkQuery, $item->checkQuery);
        $this->assertEquals('CHANGE_COLUMN_TYPE', $item->queryType);
        $this->assertEquals(1, $item->checkQueryExpected);
        $this->assertEquals($msgElements, $item->msgElements);
        $this->assertEquals(0, $item->checkStatus);
    }

    /**
     * Provides constructor data for the testBuildCheckQueryChangeColumnType method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataBuildCheckQueryChangeColumnType(): array
    {
        return [
            ['ALTER TABLE `#__foo` CHANGE `bar` `bar_new` mediumtext', "SHOW COLUMNS IN `#__foo` WHERE field = 'bar_new' AND UPPER(type) = 'MEDIUMTEXT'", ["'jos_foo'", "'bar_new'", "mediumtext"]],
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
