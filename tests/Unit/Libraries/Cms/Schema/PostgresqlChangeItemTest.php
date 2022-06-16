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
use Joomla\Database\DatabaseDriver;

class PostgresqlChangeItemTest extends \PHPUnit\Framework\TestCase
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

		$this->db->expects($this->once())
			->method('getServerType')
			->willReturn('postgresql');

		$this->db->expects($this->any())
			->method('getPrefix')
			->willReturn('jos_');

		$this->db->expects($this->any())
			->method('quote')->will(
				$this->returnCallback(function ($arg) {
					return "'" . $arg . "'";
				})
			);

		$this->db->expects($this->any())
			->method('quoteName')->will(
				$this->returnCallback(function ($arg) {
					return '"' . $arg . '"';
				})
			);
	}

	/**
	 * Provides constructor data for test methods
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function constructData(): array
	{
		return [
			[
				['query' => 'WHATEVER'],
				[
					'checkQuery' => null,
					'queryType' => null,
					'checkQueryExpected' => 1,
					'msgElements' => [],
					'checkStatus' => -1,
				],
			],
			[
				['query' => 'ALTER TABLE "#__foo" ADD COLUMN "bar" text'],
				[
					'checkQuery' => "SELECT column_name FROM information_schema.columns WHERE table_name='jos_foo' AND column_name='bar'",
					'queryType' => 'ADD_COLUMN',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'", "'bar'"],
					'checkStatus' => 0,
				],
			],
			[
				['query' => 'ALTER TABLE "#__foo" DROP COLUMN "bar"'],
				[
					'checkQuery' => "SELECT column_name FROM information_schema.columns WHERE table_name='jos_foo' AND column_name='bar'",
					'queryType' => 'DROP_COLUMN',
					'checkQueryExpected' => 0,
					'msgElements' => ["'jos_foo'", "'bar'"],
					'checkStatus' => 0,
				],
			],
			[
				['query' => 'ALTER TABLE "#__foo" RENAME TO "#__bar"'],
				[
					'checkQuery' => "SELECT table_name FROM information_schema.tables WHERE table_name='jos_bar'",
					'queryType' => 'RENAME_TABLE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_bar'"],
					'checkStatus' => 0,
				],
			],
			/*
			 * The following "CREATE TABLE" statement is the shortest possible.
			 * It is valid SQL, but currently the database schema check doesn't
			 * understand it because it's shorter than expected.
			 * This is a bug which has to be fixed, then this test has to be adapted
			 * and this comment can be removed.
			 */
			[
				['query' => 'CREATE TABLE "#__foo" ("bar" text)'],
				[
					'checkQuery' => null,
					'queryType' => null,
					'checkQueryExpected' => 1,
					'msgElements' => [],
					'checkStatus' => -1,
				],
			],
			[
				['query' => 'CREATE TABLE IF NOT EXISTS "#__foo" ("bar" text)'],
				[
					'checkQuery' => "SELECT table_name FROM information_schema.tables WHERE table_name='jos_foo'",
					'queryType' => 'CREATE_TABLE',
					'checkQueryExpected' => 1,
					'msgElements' => ["'jos_foo'"],
					'checkStatus' => 0,
				],
			],
		];
	}

	/**
	 * @param   array  $options  Options array to inject
	 * @param   array  $expects  Expected data values
	 *
	 * @dataProvider constructData
	 *
	 * @return void
	 * @since   __DEPLOY_VERSION__
	 */
	public function testBuildCheckQuery($options, $expects)
	{
		$message = "Test '%s' for query '". $options['query'] . "' failed.";
		$item    = PostgresqlChangeItem::getInstance($this->db, '/not/really/used/4.0.0-2018-03-05.sql', $options['query']);

		$this->assertEquals($expects['checkQuery'], $item->checkQuery, sprintf($message, 'checkQuery'));
		$this->assertEquals($expects['queryType'], $item->queryType, sprintf($message, 'queryType'));
		$this->assertEquals($expects['checkQueryExpected'], $item->checkQueryExpected, sprintf($message, 'checkQueryExpected'));
		$this->assertEquals($expects['msgElements'], $item->msgElements, sprintf($message, 'msgElements'));
		$this->assertEquals($expects['checkStatus'], $item->checkStatus, sprintf($message, 'checkStatus'));
	}
}
