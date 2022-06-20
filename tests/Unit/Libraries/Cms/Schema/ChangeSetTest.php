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
		if (!is_dir(__DIR__ . '/tmp'))
		{
			mkdir(__DIR__ . '/tmp');
		}

		touch(__DIR__ . '/tmp/4.1.0-2022-07-01.sql');
		touch(__DIR__ . '/tmp/4.2.0-2022-05-31.sql');
		touch(__DIR__ . '/tmp/4.2.0-2022-06-01.sql');
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
		if (is_dir(__DIR__ . '/tmp'))
		{
			Folder::delete(__DIR__ . '/tmp');
		}
	}

	/**
	 * @testdox  the schema's status is correctly initialized
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testChangeSetGetStatus()
	{
		$db = $this->createStub(DatabaseDriver::class);

		// Use postgresql because this doesn't require utf8mb4 specific methods which exists in the MysqliDriver only
		$db->method('getServerType')->willReturn('postgresql');

		$changeSet = new ChangeSet($db, __DIR__ . '/tmp');

		$status = $changeSet->getStatus();

		$this->assertArrayHasKey('unchecked', $status);
		$this->assertArrayHasKey('ok', $status);
		$this->assertArrayHasKey('error', $status);
		$this->assertArrayHasKey('skipped', $status);

		$this->assertEquals([], $status['unchecked'], 'There should not be any unchecked items');
		$this->assertEquals([], $status['ok'], 'There should not be any checked items');
		$this->assertEquals([], $status['error'], 'There should not be any items with errors');
		$this->assertEquals([], $status['skipped'], 'There should not be any skipped items');
	}

	/**
	 * @testdox  returns the latest schema version
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testChangeSetGetSchema()
	{
		$db = $this->createStub(DatabaseDriver::class);

		$changeSet = new ChangeSet($db, __DIR__ . '/tmp');

		$this->assertSame('4.2.0-2022-06-01', $changeSet->getSchema());
	}
}
