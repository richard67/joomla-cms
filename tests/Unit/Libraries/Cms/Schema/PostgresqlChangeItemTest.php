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
     * @param   string  $query  update statement to be skipped
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
            ['CREATE TABLE "#__foo" ("bar" text)'],
            ['CREATE TABLE #__foo ("bar" text)'],
            ['CREATE TABLE IF NOT EXISTS "#__foo" ("bar" text)'],
            ['CREATE TABLE IF NOT EXISTS #__foo ("bar" text)'],
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
     * @param   string  $query  ADD_COLUMN statement
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
     * @param   string  $query  DROP_COLUMN statement
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

        $this->assertEquals("SELECT * FROM pg_indexes WHERE indexname='jos_foo_idx_bar' AND tablename='jos_foo'", $item->checkQuery);
        $this->assertEquals('ADD_INDEX', $item->queryType);
        $this->assertEquals(1, $item->checkQueryExpected);
        $this->assertEquals(["'jos_foo'", "'#__foo_idx_bar'"], $item->msgElements);
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
            ['CREATE INDEX "#__foo_idx_bar" ON "#__foo" ("bar")'],
            ['CREATE UNIQUE INDEX "#__foo_idx_bar" ON "#__foo" ("bar")'],
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

        $this->assertEquals("SELECT * FROM pg_indexes WHERE indexname='jos_foo_idx_bar'", $item->checkQuery);
        $this->assertEquals('DROP_INDEX', $item->queryType);
        $this->assertEquals(0, $item->checkQueryExpected);
        $this->assertEquals(["'#__foo_idx_bar'"], $item->msgElements);
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
            ['DROP INDEX "#__foo_idx_bar"'],
            ['DROP INDEX IF EXISTS "#__foo_idx_bar"'],
        ];
    }
}
