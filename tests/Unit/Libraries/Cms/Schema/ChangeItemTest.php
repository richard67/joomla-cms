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
	 * Provides constructor data for the testGetInstanceSubclass method
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dataGetInstanceSubclass(): array
	{
		return [
			// 'data set name' => ['database server type', 'ChangeItem subclass']
			'MySQL'      => ['mysql', MysqlChangeItem::class],
			'PostgreSQL' => ['postgresql', PostgresqlChangeItem::class],
		];
	}
}
