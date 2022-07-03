<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Schema;

use Joomla\CMS\Schema\ChangeItem\PostgresqlChangeItem;
use Joomla\Database\Pgsql\PgsqlDriver;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Schema\ChangeItem\PostgresqlChangeItem
 *
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 *
 * @testdox     The PostgresqlChangeItem
 *
 * @since       __DEPLOY_VERSION__
 */
class PostgresqlChangeItemTest extends UnitTestCase
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
        $db = $this->createStub(PgsqlDriver::class);

        $item = new PostgresqlChangeItem($db, '', $query);

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
            [null],
            [''],
            ['WHATEVER'],
        ];
    }

    /**
     * @testdox  can build the right query for CREATE_TABLE statements
     *
     * @dataProvider  dataBuildCheckQueryCreateTable
     *
     * @param   array  $query  CREATE_TABLE statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryCreateTable($query)
    {
        $db = $this->createStub(PgsqlDriver::class);
        $db->method('getPrefix')->willReturn('jos_');
        $db->method('quote')->will(
            $this->returnCallback(function ($arg) {
                return "'" . $arg . "'";
            })
        );
        $db->method('quoteName')->will(
            $this->returnCallback(function ($arg) {
                return '"' . $arg . '"';
            })
        );

        $item = new PostgresqlChangeItem($db, '', $query);

        $this->assertEquals("SELECT table_name FROM information_schema.tables WHERE table_name='jos_foo'", $item->checkQuery);
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
            //['CREATE TABLE "#__foo" ("bar" text)'],
            //['CREATE TABLE #__foo ("bar" text)'],
            // The following 2 tests are obsolete when the above bug has been fixed
            ['CREATE TABLE "#__foo" ("bar" text NOT NULL)'],
            ['CREATE TABLE #__foo ("bar" text NOT NULL)'],
            ['CREATE TABLE IF NOT EXISTS "#__foo" ("bar" text)'],
            ['CREATE TABLE IF NOT EXISTS #__foo ("bar" text)'],
        ];
    }

    /**
     * @testdox  can build the right query for RENAME_TABLE statements
     *
     * @dataProvider  dataBuildCheckQueryRenameTable
     *
     * @param   array  $query  RENAME_TABLE statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryRenameTable($query)
    {
        $db = $this->createStub(PgsqlDriver::class);
        $db->method('getPrefix')->willReturn('jos_');
        $db->method('quote')->will(
            $this->returnCallback(function ($arg) {
                return "'" . $arg . "'";
            })
        );
        $db->method('quoteName')->will(
            $this->returnCallback(function ($arg) {
                return '"' . $arg . '"';
            })
        );

        $item = new PostgresqlChangeItem($db, '', $query);

        $this->assertEquals("SELECT table_name FROM information_schema.tables WHERE table_name='jos_bar'", $item->checkQuery);
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
            ['ALTER TABLE "#__foo" RENAME TO "#__bar"'],
            ['ALTER TABLE #__foo RENAME TO #__bar'],
        ];
    }

    /**
     * @testdox  can build the right query for ADD_COLUMN statements
     *
     * @dataProvider  dataBuildCheckQueryAddColumn
     *
     * @param   array  $query  ADD_COLUMN statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryAddColumn($query)
    {
        $db = $this->createStub(PgsqlDriver::class);
        $db->method('getPrefix')->willReturn('jos_');
        $db->method('quote')->will(
            $this->returnCallback(function ($arg) {
                return "'" . $arg . "'";
            })
        );
        $db->method('quoteName')->will(
            $this->returnCallback(function ($arg) {
                return '"' . $arg . '"';
            })
        );

        $item = new PostgresqlChangeItem($db, '', $query);

        $this->assertEquals("SELECT column_name FROM information_schema.columns WHERE table_name='jos_foo' AND column_name='bar'", $item->checkQuery);
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
            ['ALTER TABLE "#__foo" ADD COLUMN "bar" text'],
            ['ALTER TABLE #__foo ADD COLUMN "bar" text'],
            ['ALTER TABLE "#__foo" ADD COLUMN bar text'],
            ['ALTER TABLE #__foo ADD COLUMN bar text'],
        ];
    }

    /**
     * @testdox  can build the right query for DROP_COLUMN statements
     *
     * @dataProvider  dataBuildCheckQueryDropColumn
     *
     * @param   array  $query  DROP_COLUMN statement
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBuildCheckQueryDropColumn($query)
    {
        $db = $this->createStub(PgsqlDriver::class);
        $db->method('getPrefix')->willReturn('jos_');
        $db->method('quote')->will(
            $this->returnCallback(function ($arg) {
                return "'" . $arg . "'";
            })
        );
        $db->method('quoteName')->will(
            $this->returnCallback(function ($arg) {
                return '"' . $arg . '"';
            })
        );

        $item = new PostgresqlChangeItem($db, '', $query);

        $this->assertEquals("SELECT column_name FROM information_schema.columns WHERE table_name='jos_foo' AND column_name='bar'", $item->checkQuery);
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
            ['ALTER TABLE "#__foo" DROP COLUMN "bar"'],
            ['ALTER TABLE #__foo DROP COLUMN "bar"'],
            ['ALTER TABLE "#__foo" DROP COLUMN bar'],
            ['ALTER TABLE #__foo DROP COLUMN bar'],
        ];
    }
}
