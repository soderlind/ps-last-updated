<?php
/**
 * PHPUnit bootstrap: loads the plugin with WordPress mocked by Brain Monkey.
 *
 * @package PSLastUpdatedAdminColumns
 */

declare( strict_types=1 );

$autoload = __DIR__ . '/../vendor/autoload.php';

if ( ! file_exists( $autoload ) ) {
	fwrite( STDERR, "Run `composer install` before running the tests.\n" );
	exit( 1 );
}

require_once $autoload;

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/wordpress/' );
}

/**
 * Minimal stand-in for WP_Screen, used for `instanceof` checks.
 */
class WP_Screen {

	/**
	 * Screen base.
	 *
	 * @var string
	 */
	public $base;

	/**
	 * Screen post type.
	 *
	 * @var string
	 */
	public $post_type;

	/**
	 * Build a screen stub.
	 *
	 * @param string $base      Screen base.
	 * @param string $post_type Screen post type.
	 */
	public function __construct( string $base = 'edit', string $post_type = 'post' ) {
		$this->base      = $base;
		$this->post_type = $post_type;
	}
}

/**
 * Minimal stand-in for WP_User.
 */
class WP_User {

	/**
	 * User ID.
	 *
	 * @var int
	 */
	public $ID; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

	/**
	 * Display name.
	 *
	 * @var string
	 */
	public $display_name;

	/**
	 * Build a user stub.
	 *
	 * @param int    $id           User ID.
	 * @param string $display_name Display name.
	 */
	public function __construct( int $id, string $display_name ) {
		$this->ID           = $id; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$this->display_name = $display_name;
	}
}

/*
 * The plugin file registers hooks on load, so `add_action()` has to exist
 * before the file is required. Brain Monkey is only needed for that one call.
 */
Brain\Monkey\setUp();
Brain\Monkey\Functions\when( 'add_action' )->justReturn( true );

require_once dirname( __DIR__ ) . '/ps-last-updated.php';

Brain\Monkey\tearDown();
