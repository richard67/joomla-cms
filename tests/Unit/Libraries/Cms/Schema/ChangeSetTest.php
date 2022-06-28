<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Schema;

use Joomla\CMS\Schema\ChangeSet;
use Joomla\CMS\Schema\ChangeItem\MysqlChangeItem;
use Joomla\CMS\Schema\ChangeItem\PostgresqlChangeItem;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Mysqli\MysqliDriver;
use Joomla\Database\Mysql\MysqlDriver;
use Joomla\Database\Pgsql\PgsqlDriver;
use Joomla\Filesystem\Folder;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Schema\ChangeSet
 *
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 *
 * @testdox     The ChangeSet
 *
 * @since       __DEPLOY_VERSION__
 */
class ChangeSetTest extends UnitTestCase
{
    /**
     * Setup
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setUp(): void
    {
        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }

        if (!is_dir(__DIR__ . '/tmp/mysql')) {
            mkdir(__DIR__ . '/tmp/mysql');
        }

        if (!is_dir(__DIR__ . '/tmp/postgresql')) {
            mkdir(__DIR__ . '/tmp/postgresql');
        }
    }

    /**
     * Cleanup
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function tearDown(): void
    {
        if (is_dir(__DIR__ . '/tmp')) {
            Folder::delete(__DIR__ . '/tmp');
        }
    }

    /**
     * @testdox  the object is instantiated correctly
     *
     * @dataProvider  dataObjectIsInstantiatedCorrectly
     *
     * @param   string  $driverSubclass  The subclass of DatabaseDriver that is expected
     * @param   string  $itemSubclass    The subclass of ChangeItem that is expected
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testObjectIsInstantiatedCorrectly($serverType, $driverSubclass, $itemSubclass)
    {
        // Make sure that there will be two change items
        file_put_contents(__DIR__ . '/tmp/' . $serverType . '/4.2.0-2022-06-01.sql', "DUMMYTEXT\n");
        file_put_contents(__DIR__ . '/tmp/' . $serverType . '/4.2.0-2022-06-02.sql', "DUMMYTEXT\n");

        $db = $this->createStub($driverSubclass);
        $db->method('getServerType')->willReturn($serverType);

        $changeSet = new ChangeSet($db, __DIR__ . '/tmp');

        // Use reflection to test protected properties
        $reflectionClass = new \ReflectionClass($changeSet);
        $changeSetDb     = $reflectionClass->getProperty('db');
        $changeItems     = $reflectionClass->getProperty('changeItems');

        $changeSetDb->setAccessible(true);
        $changeItems->setAccessible(true);

        $this->assertInstanceOf($driverSubclass, $changeSetDb->getValue($changeSet), 'The database driver should be correctly injected');

        $this->assertContainsOnlyInstancesOf($itemSubclass, $changeItems->getValue($changeSet), 'The change items should have the right subclass');

        $this->assertEquals(2, count($changeItems->getValue($changeSet)), 'There should be two change items');
    }

    /**
     * Provides data for the testObjectIsInstantiatedCorrectly method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataObjectIsInstantiatedCorrectly(): array
    {
        return [
            // 'test set name' => ['database server type', 'DatabaseDriver subclass', 'ChangeItem subclass']
            'MySQLi'           => ['mysql', MysqliDriver::class, MysqlChangeItem::class],
            'MySQL (PDO)'      => ['mysql', MysqlDriver::class, MysqlChangeItem::class],
            'PostgreSQL (PDO)' => ['postgresql', PgsqlDriver::class, PostgresqlChangeItem::class],
        ];
    }

    /**
     * @testdox  the check method runs the check method of each check item and returns an array with unsuccessfully check items
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    /*public function testCheck()
    {
    }*/

    /**
     * @testdox  the schema's status is correctly initialized
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetStatus()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        $changeSet = new ChangeSet($db, __DIR__ . '/tmp');

        // Use reflection to test protected property
        $reflectionClass = new \ReflectionClass($changeSet);
        $changeItems     = $reflectionClass->getProperty('changeItems');

        $changeItems->setAccessible(true);

        $status = $changeSet->getStatus();

        $this->assertArrayHasKey('unchecked', $status);
        $this->assertArrayHasKey('ok', $status);
        $this->assertArrayHasKey('error', $status);
        $this->assertArrayHasKey('skipped', $status);

        $this->assertEquals([], $status['unchecked'], 'There should not be any unchecked change items');
        $this->assertEquals([], $status['ok'], 'There should not be any checked change items');
        $this->assertEquals([], $status['error'], 'There should not be any change items with errors');
        //$this->assertEquals($changeItems->getValue($changeSet), $status['skipped'], 'All change items should be skipped');
    }

    /**
     * @testdox  returns the latest schema version
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetSchema()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        touch(__DIR__ . '/tmp/mysql/4.0.6-2021-12-23.sql');
        touch(__DIR__ . '/tmp/mysql/4.1.0-2021-11-20.sql');
        touch(__DIR__ . '/tmp/mysql/4.1.0-2021-11-28.sql');

        $changeSet = new ChangeSet($db, __DIR__ . '/tmp');

        $this->assertSame('4.1.0-2021-11-28', $changeSet->getSchema());
    }
}
