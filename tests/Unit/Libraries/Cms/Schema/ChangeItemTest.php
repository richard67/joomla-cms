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
use Joomla\Database\DatabaseDriver;
use Joomla\Tests\Unit\UnitTestCase;

class ChangeItemTest extends UnitTestCase
{
	/**
	 * @var  DatabaseDriver|MockObject
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * Sets up the database mock.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function setUp():void
	{
		$this->db = $this->createMock(DatabaseDriver::class);
	}

	/**
	 * Data provider for the getInstance() test case
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dataGetInstance(): array
	{
		return [
			'MySQL'      => ['mysql', 'Mysql'],
			'PostgreSQL' => ['postgresql', 'Postgresql'],
		];
	}

	/**
	 * @testdox  A ChangeItem instance is retreived with the right subclass for the given database server type
	 *
	 * @covers        Joomla\CMS\Schema\ChangeItem::getInstance
	 * @dataProvider  dataGetInstance
	 *
	 * @param   string  $servertype    The value to be returned by the getServerType method of the database driver
	 * @param   string  $itemSubclass  The subclass of ChangeItem that is expected
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetInstanceReturnsTheCorrectObject($serverType, $itemSubclass)
	{
		$this->db->expects($this->once())
			->method('getServerType')
			->willReturn($serverType);

		$item = ChangeItem::getInstance($this->db, '/not/really/used/4.0.0-2018-03-05.sql', 'QUERY NOT REALLY USED');

		$this->assertInstanceOf('\\Joomla\\CMS\\Schema\\ChangeItem\\' . $itemSubclass . 'ChangeItem', $item, 'The correct ChangeItem subclass was not instantiated');
	}

	/**
	 * @testdox  A runtime exception is thrown when trying to retrieve a ChangeItem instance for an invalid database server type
	 *
	 * @covers  Joomla\CMS\Schema\ChangeItem::getInstance
	 *
	 * @return  void
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetInstanceThrowsAnExceptionForAnUnsupportedDbServerType()
	{
		$this->db->expects($this->once())
			->method('getServerType')
			->willReturn('sqlite');

		$this->expectException(\RuntimeException::class);

		$item = ChangeItem::getInstance($this->db, '/not/really/used/4.0.0-2018-03-05.sql', 'QUERY NOT REALLY USED');
	}
}
