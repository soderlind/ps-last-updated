<?php
/**
 * Shared test case wiring Brain Monkey in and out of each test.
 *
 * @package PSLastUpdatedAdminColumns
 */

declare( strict_types=1 );

namespace PS\LastUpdated\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use PS_Last_Updated_Admin_Columns;

abstract class TestCase extends PHPUnitTestCase {

	use MockeryPHPUnitIntegration;

	/**
	 * System under test.
	 *
	 * @var PS_Last_Updated_Admin_Columns
	 */
	protected $columns;

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\stubTranslationFunctions();
		Functions\stubEscapeFunctions();

		$this->columns = new PS_Last_Updated_Admin_Columns();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Capture everything a callback echoes.
	 *
	 * @param callable $callback Callback to run.
	 * @return string
	 */
	protected function capture( callable $callback ): string {
		ob_start();
		$callback();

		return (string) ob_get_clean();
	}
}
