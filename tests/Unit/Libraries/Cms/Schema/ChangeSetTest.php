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
     * @testdox  object is instantiated correctly
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
        $db = $this->createStub($driverSubclass);
        $db->method('getServerType')->willReturn($serverType);

        // Make sure that there will not be added an extra change item for utf8mb4 conversion when database server type is mysql
        $db->method('loadRowList')->willReturn([]);

        // Make sure that there will be three change items in two files
        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }
        if (!is_dir(__DIR__ . '/tmp/' . $serverType)) {
            mkdir(__DIR__ . '/tmp/' . $serverType);
        }
        file_put_contents(__DIR__ . '/tmp/' . $serverType . '/4.2.0-2022-06-01.sql', 'UPDATE #__foo SET bar = 1;' . "\n" . 'UPDATE #__foo SET bar = 2;');
        file_put_contents(__DIR__ . '/tmp/' . $serverType . '/4.2.0-2022-06-02.sql', 'UPDATE #__foo SET bar = 3;');

        $changeSet = new class ($db, __DIR__ . '/tmp') extends ChangeSet
        {
            // Add method to get protected db property for testing
            public function changeSetTestGetDatabase()
            {
                return $this->db;
            }
            // Add method to get protected changeItems property for testing
            public function changeSetTestGetChangeItems()
            {
                return $this->changeItems;
            }
        };

        $this->assertInstanceOf($driverSubclass, $changeSet->changeSetTestGetDatabase(), 'The database driver should be correctly injected');
        $this->assertContainsOnlyInstancesOf($itemSubclass, $changeSet->changeSetTestGetChangeItems(), 'The change items should have the right subclass');
        $this->assertEquals(3, count($changeSet->changeSetTestGetChangeItems()), 'There should be three change items');
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
     * @testdox  has no change items when the folder for update files doesn't exist
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNoChangeItemsWhenFolderNotExists()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        // Make sure that there will not be added an extra change item for utf8mb4 conversion
        $db->method('loadRowList')->willReturn([]);

        $changeSet = new class ($db, __DIR__ . '/notExistingFolder') extends ChangeSet
        {
            // Add method to get protected changeItems property for testing
            public function changeSetTestGetChangeItems()
            {
                return $this->changeItems;
            }
        };

        $this->assertEquals([], $changeSet->changeSetTestGetChangeItems(), 'There should be no change items');
    }

    /**
     * @testdox  has no change items when the folder for update files is empty
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNoChangeItemsWhenEmptyFolder()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        // Make sure that there will not be added an extra change item for utf8mb4 conversion
        $db->method('loadRowList')->willReturn([]);

        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }
        if (!is_dir(__DIR__ . '/tmp/mysql')) {
            mkdir(__DIR__ . '/tmp/mysql');
        }

        $changeSet = new class ($db, __DIR__ . '/tmp') extends ChangeSet
        {
            // Add method to get protected changeItems property for testing
            public function changeSetTestGetChangeItems()
            {
                return $this->changeItems;
            }
        };

        $this->assertEquals([], $changeSet->changeSetTestGetChangeItems(), 'There should be no change items');
    }

    /**
     * @testdox  has no change items when there are no update queries
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testNoChangeItemsWhenNoUpdateQueries()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        // Make sure that there will not be added an extra change item for utf8mb4 conversion
        $db->method('loadRowList')->willReturn([]);

        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }
        if (!is_dir(__DIR__ . '/tmp/mysql')) {
            mkdir(__DIR__ . '/tmp/mysql');
        }
        touch(__DIR__ . '/tmp/mysql/4.1.0-2021-11-20.sql');

        $changeSet = new class ($db, __DIR__ . '/tmp') extends ChangeSet
        {
            // Add method to get protected changeItems property for testing
            public function changeSetTestGetChangeItems()
            {
                return $this->changeItems;
            }
        };

        $this->assertEquals([], $changeSet->changeSetTestGetChangeItems(), 'There should be no change items');
    }

    /**
     * @testdox  uses the core com_admin folder as default
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testUseComAdminAsDefaultFolder()
    {
        $db = $this->createStub(DatabaseDriver::class);

        // Use server type postgresql to not run special utf8mb4 checks for MySQL
        $db->method('getServerType')->willReturn('postgresql');

        // Create a change set without the folder parameter
        $changeSet = new class ($db) extends ChangeSet
        {
            // Skip getting update files for this test
            private function getUpdateFiles()
            {
                return false;
            }
            // Add method to get protected folder property for testing
            public function changeSetTestGetFolder()
            {
                return $this->folder;
            }
        };

        $this->assertEquals(JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/', $changeSet->changeSetTestGetFolder());
    }

    /**
     * @testdox  adds a change item to check utf8mb4 conversion status on mysql servers if the #__utf8_conversion table exists
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testAddChangeItemForUtf8mb4Check()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');
        $db->method('quoteName')->will(
            $this->returnCallback(function ($arg) {
                return '`' . $arg . '`';
            })
        );

        // Make sure that there will be a #__utf8_conversion table found
        $db->method('loadRowList')->willReturn([0 => [0 => 'jos_utf8_conversion']]);

        // Make sure that there will be two change items
        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }
        if (!is_dir(__DIR__ . '/tmp/mysql')) {
            mkdir(__DIR__ . '/tmp/mysql');
        }
        file_put_contents(__DIR__ . '/tmp/mysql/4.2.0-2022-06-01.sql', 'UPDATE #__foo SET bar = 1;' . "\n" . 'UPDATE #__foo SET bar = 2;');

        $changeSet = new class ($db, __DIR__ . '/tmp') extends ChangeSet
        {
            // Add method to get protected changeItems property for testing
            public function changeSetTestGetChangeItems()
            {
                return $this->changeItems;
            }
        };

        $itemExpected = new MysqlChangeItem($db, 'database.php', 'UPDATE `#__utf8_conversion` SET `converted` = `converted`;');
        $itemExpected->checkStatus = 0;
        $itemExpected->queryType = 'UTF8_CONVERSION_UTF8MB4';
        $itemExpected->checkQuery = 'SELECT `converted` FROM `#__utf8_conversion` WHERE `converted` = 5';
        $itemExpected->checkQueryExpected = 1;
        $itemExpected->msgElements = [];

        $this->assertEquals(3, count($changeSet->changeSetTestGetChangeItems()), 'There should be three change items');
        $this->assertEquals($itemExpected, $changeSet->changeSetTestGetChangeItems()[2], 'The last change item should be the utf8mb4 conversion check');
    }

    /**
     * @testdox  doesn't add a change item to check utf8mb4 conversion status if the check for the #__utf8_conversion table fails with a runtime exception
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testDoNotAddChangeItemForUtf8mb4CheckIfRuntimeException()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');
        $db->method('quoteName')->will(
            $this->returnCallback(function ($arg) {
                return '`' . $arg . '`';
            })
        );

        // Make sure that the check for the #__utf8_conversion table raises a runtime exception
        $db->method('loadRowList')->will($this->throwException(new \RuntimeException('Exception message')));

        // Make sure that there will a change item
        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }
        if (!is_dir(__DIR__ . '/tmp/mysql')) {
            mkdir(__DIR__ . '/tmp/mysql');
        }
        file_put_contents(__DIR__ . '/tmp/mysql/4.2.0-2022-06-01.sql', 'UPDATE #__foo SET bar = 1;');

        $changeSet = new class ($db, __DIR__ . '/tmp') extends ChangeSet
        {
            // Add method to get protected changeItems property for testing
            public function changeSetTestGetChangeItems()
            {
                return $this->changeItems;
            }
        };

        $this->assertEquals(1, count($changeSet->changeSetTestGetChangeItems()), 'There should be one change item');
        $this->assertNotEquals('UTF8_CONVERSION_UTF8MB4', $changeSet->changeSetTestGetChangeItems()[0], 'There should not be a utf8mb4 conversion check');
    }

    /**
     * @testdox  can return a reference to the ChangeSet object, only creating it if it doesn't already exist
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetInstance()
    {
        $db = $this->createStub(DatabaseDriver::class);

        // Use server type postgresql to not run special utf8mb4 checks for MySQL
        $db->method('getServerType')->willReturn('postgresql');

        $changeSet1 = ChangeSet::getInstance($db, __DIR__ . '/tmp');
        $changeSet2 = ChangeSet::getInstance($db, __DIR__ . '/tmp');

        $this->assertSame($changeSet1, $changeSet2, 'The getInstance method should not create a new object on consecutive static calls');
    }

    /**
     * @testdox  can call the check method of each change item and return an empty array if all items were checked with success
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckAllItemsWithSuccess()
    {
        // Create a change set without any change items
        $changeSet = new class ($this->createStub(DatabaseDriver::class)) extends ChangeSet
        {
            public function __construct($db, $folder = null)
            {
            }
            // Add method to set protected changeItems property for testing
            public function changeSetTestSetChangeItems($items)
            {
                $this->changeItems = $items;
            }
        };

        // Create an array with 3 change item stubs which will be checked with success
        $items = [];
        for ($i = 0; $i < 3; $i++) {
            $item = $this->createStub(ChangeItem::class);
            // Make sure the check method is called one time and returns success
            $item->expects($this->once())->method('check')->willReturn(1);
            $items[] = $item;
        }

        // Set change set's change items to the previously created array
        $changeSet->changeSetTestSetChangeItems($items);

        $this->assertEquals([], $changeSet->check());
    }

    /**
     * @testdox  can call the check method of each change item and return an array of check items which have been checked with error
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckItemsWithError()
    {
        // Create a change set without any change items
        $changeSet = new class ($this->createStub(DatabaseDriver::class)) extends ChangeSet
        {
            public function __construct($db, $folder = null)
            {
            }
            // Add method to set protected changeItems property for testing
            public function changeSetTestSetChangeItems($items)
            {
                $this->changeItems = $items;
            }
        };

        // Create an array with 3 change item stubs
        $items = [];
        for ($i = 0; $i < 3; $i++) {
            $items[] = $this->createStub(ChangeItem::class);
        }

        // Each check item's check method shall be called one time and return the desired value
        $items[0]->expects($this->once())->method('check')->willReturn(-2); // return error
        $items[1]->expects($this->once())->method('check')->willReturn(1); // return success
        $items[2]->expects($this->once())->method('check')->willReturn(-2); // return error

        // Set change set's change items to the previously created array
        $changeSet->changeSetTestSetChangeItems($items);

        $this->assertEquals([$items[0], $items[2]], $changeSet->check());
    }

    /**
     * @testdox  can run each change item's fix method
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testFix()
    {
        // Create a change set without any change items
        $changeSet = new class ($this->createStub(DatabaseDriver::class)) extends ChangeSet
        {
            public function __construct($db, $folder = null)
            {
            }
            // Add method to set protected changeItems property for testing
            public function changeSetTestSetChangeItems($items)
            {
                $this->changeItems = $items;
            }
        };

        $items = [];

        // Create an array with 3 change item stubs which will be checked with success
        for ($i = 0; $i < 3; $i++) {
            $item = $this->createStub(ChangeItem::class);

            // Make sure the fix method is called one time
            $item->expects($this->once())->method('fix');
            $items[] = $item;
        }

        // Set change set's change items to the previously created array
        $changeSet->changeSetTestSetChangeItems($items);

        $changeSet->fix();
    }

    /**
     * @testdox  can return an array of change items grouped by their check status
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetStatus()
    {
        $db = $this->createStub(DatabaseDriver::class);

        // Create a change set without any change items
        $changeSet = new class ($db) extends ChangeSet
        {
            public function __construct($db, $folder = null)
            {
            }
            // Add method to set protected changeItems property for testing
            public function changeSetTestSetChangeItems($items)
            {
                $this->changeItems = $items;
            }
        };

        // Create an array with 8 change items
        $items = [];
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
        $changeSet->changeSetTestSetChangeItems($items);

        $status = $changeSet->getStatus();
        $this->assertEquals([$items[0], $items[7]], $status['unchecked'], 'The unchecked status should contain the right change items');
        $this->assertEquals([$items[1], $items[6]], $status['ok'], 'The ok status should contain the right change items');
        $this->assertEquals([$items[2], $items[5]], $status['error'], 'The error status should contain the right change items');
        $this->assertEquals([$items[3], $items[4]], $status['skipped'], 'The skipped status should contain the right change items');
    }

    /**
     * @testdox  can return the latest schema version
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetSchema()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        // Make sure that there will not be added an extra change item for utf8mb4 conversion
        $db->method('loadRowList')->willReturn([]);

        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }
        if (!is_dir(__DIR__ . '/tmp/mysql')) {
            mkdir(__DIR__ . '/tmp/mysql');
        }
        touch(__DIR__ . '/tmp/mysql/4.1.0-2021-11-20.sql');
        touch(__DIR__ . '/tmp/mysql/4.1.0-2021-11-28.sql');
        touch(__DIR__ . '/tmp/mysql/4.0.6-2021-12-23.sql');

        $changeSet = new ChangeSet($db, __DIR__ . '/tmp');
        $this->assertSame('4.1.0-2021-11-28', $changeSet->getSchema());
    }

    /**
     * @testdox  can return an empty string as schema version if there are no update files
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetSchemaNoUpdateFiles()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('mysql');

        // Make sure that there will not be added an extra change item for utf8mb4 conversion
        $db->method('loadRowList')->willReturn([]);

        if (!is_dir(__DIR__ . '/tmp')) {
            mkdir(__DIR__ . '/tmp');
        }
        if (!is_dir(__DIR__ . '/tmp/mysql')) {
            mkdir(__DIR__ . '/tmp/mysql');
        }

        $changeSet = new ChangeSet($db, __DIR__ . '/tmp');
        $this->assertSame('', $changeSet->getSchema());
    }
}
