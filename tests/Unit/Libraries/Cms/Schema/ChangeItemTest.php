<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Schema;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Schema\ChangeItem;
use Joomla\CMS\Schema\ChangeItem\MysqlChangeItem;
use Joomla\CMS\Schema\ChangeItem\PostgresqlChangeItem;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Schema\ChangeItem
 *
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 *
 * @testdox     The ChangeItem
 *
 * @since       __DEPLOY_VERSION__
 */
class ChangeItemTest extends UnitTestCase
{
    /**
     * @testdox  has the right subclass for the given database server type
     *
     * @dataProvider  dataGetInstanceSubclass
     *
     * @param   string  $servertype    The value returned by the getServerType method of the database driver
     * @param   string  $itemSubclass  The subclass of ChangeItem that is expected
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetInstanceSubclass($serverType, $itemSubclass)
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn($serverType);

        $item = ChangeItem::getInstance($db, '', '');

        $this->assertInstanceOf($itemSubclass, $item);
    }

    /**
     * Provides data for the testGetInstanceSubclass method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataGetInstanceSubclass(): array
    {
        return [
            // ['database server type', 'ChangeItem subclass']
            ['mysql', MysqlChangeItem::class],
            ['postgresql', PostgresqlChangeItem::class],
        ];
    }

    /**
     * @testdox  throws a runtime exception if the database server type is not supported
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testGetInstanceUnsupportedDatabaseType()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('getServerType')->willReturn('sqlite');

        $this->expectException(\RuntimeException::class);

        $item = ChangeItem::getInstance($db, '', '');
    }

    /**
     * @testdox  is skipped if it has no check query
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testEmptyCheckQuery()
    {
        $item = new class ($this->createStub(DatabaseDriver::class), '', '') extends ChangeItem
        {
            public function check()
            {
                return parent::check();
            }

            public function buildCheckQuery()
            {
            }
        };

        $item->checkQuery = null;
        $item->check();
        $this->assertEquals(-1, $item->checkStatus, 'The ChangeItem should be skipped if the check query is null');

        $item->checkQuery = '';
        $item->check();
        $this->assertEquals(-1, $item->checkStatus, 'The ChangeItem should be skipped if the check query is empty');
    }

    /**
     * @testdox  sets the right check status if the check query returns no result
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckQueryWithoutResult()
    {
        $db = $this->createStub(DatabaseDriver::class);

        // Let the loadRowList method of the driver return no result
        $db->method('loadRowList')->willReturn([]);

        $item = new class ($db, '', '') extends ChangeItem
        {
            public function check()
            {
                return parent::check();
            }

            public function buildCheckQuery()
            {
            }
        };

        // Let the check query be not empty
        $item->checkQuery = 'Something';

        // Check with success if no result is returned as expected
        $item->checkQueryExpected = 0;
        $item->check();
        $this->assertEquals(1, $item->checkStatus, 'The ChangeItem should be checked with success');

        // Check with error if no result is returned but one is expected
        $item->checkQueryExpected = 1;
        $item->check();
        $this->assertEquals(-2, $item->checkStatus, 'The ChangeItem should be checked with error');
    }

    /**
     * @testdox  sets the right check status if the check query returns a result
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckQueryWithOneResult()
    {
        $db = $this->createStub(DatabaseDriver::class);

        // Let the loadRowList method of the driver return one result
        $db->method('loadRowList')->willReturn(['Something']);

        $item = new class ($db, '', '') extends ChangeItem
        {
            public function check()
            {
                return parent::check();
            }

            public function buildCheckQuery()
            {
            }
        };

        // Let the check query be not empty
        $item->checkQuery = 'Something';

        // Check with success if one result is returned as expected
        $item->checkQueryExpected = 1;
        $item->check();
        $this->assertEquals(1, $item->checkStatus, 'The ChangeItem should be checked with success');

        // Check with error if one result is returned but none is expected
        $item->checkQueryExpected = 0;
        $item->check();
        $this->assertEquals(-2, $item->checkStatus, 'The ChangeItem should be checked with error');
    }

    /**
     * @testdox  sets the check status to error if the check query fails with a RuntimeException
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckQueryRuntimeException()
    {
        $db = $this->createStub(DatabaseDriver::class);

        // Let the loadRowList method of the driver return no result
        $db->method('loadRowList')->willReturn([]);

        $db->method('setQuery')->will($this->throwException(new \RuntimeException('Exception message')));

        $app = $this->createStub(CMSApplicationInterface::class);

        $item = new class ($db, '', '') extends ChangeItem
        {
            public function check()
            {
                return parent::check();
            }

            public function buildCheckQuery()
            {
            }
        };

        $item->setApplication($app);

        // Let the check query be not empty
        $item->checkQuery = 'Something';

        // Make sure the item would be checked with success if no RuntimeException
        $item->checkQueryExpected = 0;

        $item->check();
        $this->assertEquals(-2, $item->checkStatus, 'The ChangeItem should be checked with error');
    }

    /**
     * @testdox  sets the rerun status to error if the update query fails with a RuntimeException
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testUpdateQueryRuntimeException()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('setQuery')->will($this->throwException(new \RuntimeException('Exception message')));

        $app = $this->createStub(CMSApplicationInterface::class);

        $item = new class ($db, '', '') extends ChangeItem
        {
            public function check()
            {
                // Return success
                return 1;
            }

            public function fix()
            {
                return parent::fix();
            }

            public function buildCheckQuery()
            {
            }
        };

        $item->setApplication($app);

        // Let the update query be not empty
        $item->updateQuery = 'Something';

        // Set check status to error
        $item->checkStatus = -2;

        $item->fix();
        $this->assertEquals(-2, $item->rerunStatus, 'The ChangeItem\'s rerunStatus should be set to error');
    }

    /**
     * @testdox  sets the rerun status to error if the update query fails with an ExecutionFailureException
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testUpdateQueryExecutionFailureException()
    {
        $db = $this->createStub(DatabaseDriver::class);
        $db->method('execute')->will($this->throwException(new ExecutionFailureException('Exception message')));

        $app = $this->createStub(CMSApplicationInterface::class);

        $item = new class ($db, '', '') extends ChangeItem
        {
            public function check()
            {
                // Return success
                return 1;
            }

            public function fix()
            {
                return parent::fix();
            }

            public function buildCheckQuery()
            {
            }
        };

        $item->setApplication($app);

        // Let the update query be not empty
        $item->updateQuery = 'Something';

        // Set check status to error
        $item->checkStatus = -2;

        $item->fix();
        $this->assertEquals(-2, $item->rerunStatus, 'The ChangeItem\'s rerunStatus should be set to error');
    }
}
