<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Html;

use JHtmlLanguage;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\FactoryInterface;
use Joomla\CMS\Factory;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for JHtmlLanguage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       __DEPLOY_VERSION__
 */
class JHtmlLanguageTest extends UnitTestCase
{
	/**
	 * @var    string  Static document instance stashed away to be restored later.
	 * @since  __DEPLOY_VERSION__
	 */
	private $_stashedDocument = null;

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setUp(): void
	{
		parent::setUp();

		$this->_stashedDocument = Factory::$document;

		Factory::$document = new Document(['factory' => $this->createMock(FactoryInterface::class)]);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function tearDown(): void
	{
		Factory::$document = $this->_stashedDocument;

		parent::tearDown();
	}

	/**
	 * Tests the JHtmlLanguage::inlineBidirectional method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInlineBidirectional()
	{
		// Tests for LTR direction
		Factory::getDocument()->setDirection('ltr');

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('This is a test.'),
			'<span dir="auto">This is a test.</span>',
			'Value should be wrapped into a span element with default direction auto when document direction is ltr'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('This is a test.', 'auto'),
			'<span dir="auto">This is a test.</span>',
			'Value should be wrapped into a span element with direction auto when document direction is ltr'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('This is a test.', 'ltr'),
			'This is a test.',
			'Value should not be changed when desired direction is same as document direction'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('.זה מבחן', 'rtl'),
			'<span dir="rtl">.זה מבחן</span>',
			'Value should be wrapped into a span element with direction rtl when document direction is ltr'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('This is a test.', 'ltr', 'bdi'),
			'This is a test.',
			'Value should not be changed when desired direction is same as document direction'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('.זה מבחן', 'rtl', 'bdi'),
			'<bdi dir="rtl">.זה מבחן</bdi>',
			'Value should be wrapped into a bdi element with direction rtl when document direction is ltr'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('Dies ist ein Test.', 'ltr', 'span', 'de'),
			'Dies ist ein Test.',
			'Value should not be changed when desired direction is same as document direction'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('.זה מבחן', 'rtl', 'span', 'he'),
			'<span dir="rtl" lang="he">.זה מבחן</span>',
			'Value should be wrapped into a span element with direction rtl and language he when document direction is ltr'
		);

		// Tests for RTL direction
		Factory::getDocument()->setDirection('rtl');

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('.זה מבחן'),
			'<span dir="auto">.זה מבחן</span>',
			'Value should be wrapped into a span element with default direction auto when document direction is rtl'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('.זה מבחן', 'auto'),
			'<span dir="auto">.זה מבחן</span>',
			'Value should be wrapped into a span element with direction auto when document direction is rtl'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('This is a test.', 'ltr'),
			'<span dir="ltr">This is a test.</span>',
			'Value should be wrapped into a span element with direction ltr when document direction is rtl'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('.זה מבחן', 'rtl'),
			'.זה מבחן',
			'Value should not be changed when desired direction is same as document direction'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('This is a test.', 'ltr', 'bdi'),
			'<bdi dir="ltr">This is a test.</bdi>',
			'Value should be wrapped into a bdi element with direction ltr when document direction is rtl'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('.זה מבחן', 'rtl', 'bdi'),
			'.זה מבחן',
			'Value should not be changed when desired direction is same as document direction'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('Dies ist ein Test.', 'ltr', 'span', 'de'),
			'<span dir="ltr" lang="de">Dies ist ein Test.</span>',
			'Value should be wrapped into a span element with direction ltr and language de when document direction is rtl'
		);

		$this->assertEquals(
			JHtmlLanguage::inlineBidirectional('.זה מבחן', 'rtl', 'span', 'he'),
			'.זה מבחן',
			'Value should not be changed when desired direction is same as document direction'
		);
	}
}
