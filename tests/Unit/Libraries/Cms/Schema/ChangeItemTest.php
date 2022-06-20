<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Schema
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Schema;

use Joomla\CMS\Schema\ChangeItem;
use Joomla\CMS\Schema\ChangeItem\MysqlChangeItem;
use Joomla\CMS\Schema\ChangeItem\PostgresqlChangeItem;
use Joomla\Database\DatabaseDriver;
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
	 * Provides constructor data for the testGetInstanceSubclass method
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
	 * @testdox  throws a runtime exception with an unsupported database server type
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
	 * @testdox  the check() method sets the right check status
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testCheckMethodSetsRightCheckStatus()
	{
		$db = $this->createStub(DatabaseDriver::class);
		$db->method('getServerType')->willReturn('mysql');
		$db->method('loadRowList')->willReturn([]);

		$item = ChangeItem::getInstance($db, '', '');

		$item->checkQuery = null;
		$item->check();
		$this->assertEquals(-1, $item->checkStatus, 'The ChangeItem should be skipped if no check query');

		$item->checkQuery = '';
		$item->check();
		$this->assertEquals(-1, $item->checkStatus, 'The ChangeItem should be skipped if the check query is empty');

		// Success if result count from loadRowList for the checkQuery is as expected
		$item->checkQuery = 'Something';
		$item->checkQueryExpected = 0;
		$item->check();
		$this->assertEquals(1, $item->checkStatus, 'The ChangeItem should be checked with success');

		// Error if result count from loadRowList for the checkQuery is not as expected
		$item->checkQueryExpected = 1;
		$item->check();
		$this->assertEquals(-2, $item->checkStatus, 'The ChangeItem should be checked with errir');
	}
}
