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
use Joomla\CMS\Schema\ChangeItem;
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
     * @testdox  is instantiated correctly
     *
     * @dataProvider  dataObjectIsInstantiatedCorrectly
     *
     * @param   string  $serverType      The database server type as returned by the driver's getServerType method
     * @param   string  $driverSubclass  The subclass of DatabaseDriver that is expected for the $serverType
     * @param   string  $itemSubclass    The subclass of ChangeItem that is expected for the $serverType
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testObjectIsInstantiatedCorrectly($serverType, $driverSubclass, $itemSubclass)
    {
        // Make sure that there will be three change items in two files
        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }
        if (!is_dir(__DIR__ . '/tmp/' . $serverType)) {
            mkdir(__DIR__ . '/tmp/' . $serverType);
        }
        file_put_contents(__DIR__ . '/tmp/' . $serverType . '/4.2.0-2022-06-01.sql', 'UPDATE #__foo SET bar = 1;' . "\n" . 'UPDATE #__foo SET bar = 2;');
        file_put_contents(__DIR__ . '/tmp/' . $serverType . '/4.2.0-2022-06-02.sql', 'UPDATE #__foo SET bar = 3;');

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

        $this->assertEquals(3, count($changeItems->getValue($changeSet)), 'There should be three change items');
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
     * @testdox  uses the core com_admin folder as default
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testDefaultFolder()
    {
        $db = $this->createStub(MysqliDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        $changeSet = new ChangeSet($db);

        // Use reflection to test protected property
        $reflectionClass = new \ReflectionClass($changeSet);

        $changeSetFolder = $reflectionClass->getProperty('folder');

        $changeSetFolder->setAccessible(true);

        $this->assertEquals(JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/', $changeSetFolder->getValue($changeSet));
    }

    /**
     * @testdox  calls the check method of each change item and returns an empty array if all items were checked with success
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckAllItemsOk()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        // Create a change set without any change items
        $changeSet = new ChangeSet($db, __DIR__ . '/tmp');

        // Use reflection to set protected property
        $reflectionClass = new \ReflectionClass($changeSet);
        $changeItems     = $reflectionClass->getProperty('changeItems');

        $changeItems->setAccessible(true);

        $items = [];

        // Create an array with 3 change item stubs which will be checked with success
        for ($i = 0; $i < 3; $i++) {
            $item = $this->createStub(ChangeItem::class);

            // Make sure the check method is called one time and returns success
            $item->expects($this->once())->method('check')->willReturn(1);
            $items[] = $item;
        }

        // Set change set's change items to the previously created array
        $changeItems->setValue($changeSet, $items);

        $errors = $changeSet->check();

        $this->assertEquals([], $errors);
    }

    /**
     * @testdox  calls the check method of each change item and returns an array of check items which have been checked with error
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckAllItemsError()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        // Create a change set without any change items
        $changeSet = new ChangeSet($db, __DIR__ . '/tmp');

        // Use reflection to set protected property
        $reflectionClass = new \ReflectionClass($changeSet);
        $changeItems     = $reflectionClass->getProperty('changeItems');

        $changeItems->setAccessible(true);

        $items = [];

        // Create an array with 3 change items which will be checked with error
        for ($i = 0; $i < 3; $i++) {
            $items[] = new class ($db, '', '') extends ChangeItem
            {
                public function check()
                {
                    // Return error
                    return -2;
                }
                public function buildCheckQuery()
                {
                }
            };
        }

        // Set change set's change items to the previously created array
        $changeItems->setValue($changeSet, $items);

        $errors = $changeSet->check();

        $this->assertEquals($items, $errors);
    }

    /**
     * @testdox  The fix method runs the change set's check method and each change item's fix method
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testFix()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        // Create a change set without any change items
        $changeSet = new ChangeSet($db, __DIR__ . '/tmp');

        // Use reflection to set protected property
        $reflectionClass = new \ReflectionClass($changeSet);
        $changeItems     = $reflectionClass->getProperty('changeItems');

        $changeItems->setAccessible(true);

        $items = [];

        // Create an array with 3 change item stubs which will be checked with success
        for ($i = 0; $i < 3; $i++) {
            $item = $this->createStub(ChangeItem::class);

            // Make sure the fix method is called one time
            $item->expects($this->once())->method('fix');
            $items[] = $item;
        }

        // Set change set's change items to the previously created array
        $changeItems->setValue($changeSet, $items);

        $changeSet->fix();
    }

    /**
     * @testdox  returns an array of change items grouped by their check status
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetStatus()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        // Create a change set without any change items
        $changeSet = new ChangeSet($db, __DIR__ . '/tmp');

        // Use reflection to set protected property
        $reflectionClass = new \ReflectionClass($changeSet);
        $changeItems     = $reflectionClass->getProperty('changeItems');

        $changeItems->setAccessible(true);

        $items = [];

        // Create an array with 8 change items
        for ($i = 0; $i < 8; $i++) {
            $items[] = new class ($db, '', '') extends ChangeItem
            {
                public function buildCheckQuery()
                {
                }
            };
        }

        // Set check status
        $items[0]->checkStatus = 0;  /* unchecked */
        $items[1]->checkStatus = 1;  /* ok */
        $items[2]->checkStatus = -2; /* error */
        $items[3]->checkStatus = -1; /* skipped */
        $items[4]->checkStatus = -1; /* skipped */
        $items[5]->checkStatus = -2; /* error */
        $items[6]->checkStatus = 1;  /* ok */
        $items[7]->checkStatus = 0;  /* unchecked */

        // Set change set's change items to the previously created array
        $changeItems->setValue($changeSet, $items);

        $status = $changeSet->getStatus();

        $this->assertEquals([$items[0], $items[7]], $status['unchecked'], 'The unchecked status should contain the right change items');
        $this->assertEquals([$items[1], $items[6]], $status['ok'], 'The ok status should contain the right change items');
        $this->assertEquals([$items[2], $items[5]], $status['error'], 'The error status should contain the right change items');
        $this->assertEquals([$items[3], $items[4]], $status['skipped'], 'The skipped status should contain the right change items');
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

        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }
        if (!is_dir(__DIR__ . '/tmp/mysql')) {
            mkdir(__DIR__ . '/tmp/mysql');
        }
        touch(__DIR__ . '/tmp/mysql/4.0.6-2021-12-23.sql');
        touch(__DIR__ . '/tmp/mysql/4.1.0-2021-11-20.sql');
        touch(__DIR__ . '/tmp/mysql/4.1.0-2021-11-28.sql');

        $changeSet = new ChangeSet($db, __DIR__ . '/tmp');

        $this->assertSame('4.1.0-2021-11-28', $changeSet->getSchema());
    }
}
