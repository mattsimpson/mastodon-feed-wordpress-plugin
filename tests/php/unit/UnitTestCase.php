<?php
/**
 * Base test case for unit tests
 *
 * Provides common setup for Brain Monkey mocking and plugin file loading.
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * Base class for unit tests with Brain Monkey
 */
abstract class UnitTestCase extends TestCase {

	/**
	 * Track if plugin has been loaded
	 */
	private static $plugin_loaded = false;

	/**
	 * Set up Brain Monkey before each individual test
	 */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Mock WordPress functions that are always needed
		$this->mockWordPressFunctions();

		// Load the plugin file once after mocks are in place
		if ( ! self::$plugin_loaded ) {
			require_once dirname( __DIR__, 3 ) . '/mastodon-feed.php';
			self::$plugin_loaded = true;
		}
	}

	/**
	 * Tear down Brain Monkey after each test
	 */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Mock WordPress functions that are called at global scope when loading the plugin
	 *
	 * IMPORTANT: Only mock functions here that:
	 * 1. Are called during plugin file loading
	 * 2. Will NEVER need custom expectations in individual tests
	 *
	 * Do NOT mock functions that tests use expect() on - let each test mock those.
	 */
	protected function mockWordPressFunctions() {
		// Mock hooks - these are called during plugin loading
		Functions\when( 'add_shortcode' )->justReturn( null );
		Functions\when( 'add_action' )->justReturn( null );
		Functions\when( 'add_filter' )->justReturn( null );
		Functions\when( 'add_settings_section' )->justReturn( null );
		Functions\when( 'add_settings_field' )->justReturn( null );
		Functions\when( 'register_setting' )->justReturn( null );
		Functions\when( 'register_block_type' )->justReturn( true );
		Functions\when( 'register_rest_route' )->justReturn( true );
		Functions\when( 'add_options_page' )->justReturn( null );

		// Mock asset functions
		Functions\when( 'plugins_url' )->alias( function( $path ) {
			return 'https://example.com/wp-content/plugins' . $path;
		} );
		Functions\when( 'wp_enqueue_script' )->justReturn( null );
		Functions\when( 'wp_enqueue_style' )->justReturn( null );
		Functions\when( 'wp_add_inline_style' )->justReturn( null );
		Functions\when( 'wp_localize_script' )->justReturn( null );

		// Mock translation functions - return the input text as-is
		Functions\when( '__' )->returnArg();
		Functions\when( 'esc_html__' )->returnArg();
		Functions\when( 'esc_html' )->returnArg();
		Functions\when( 'esc_attr' )->returnArg();
		Functions\when( 'esc_url' )->returnArg();

		// Mock shortcode functions
		Functions\when( 'shortcode_atts' )->alias( function( $defaults, $atts ) {
			return array_merge( $defaults, (array) $atts );
		} );

		// Mock sanitization functions - return the input as-is
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_kses_post' )->returnArg();

		// Mock checked() function
		Functions\when( 'checked' )->alias( function( $checked, $current = true, $echo = true ) {
			$result = $checked == $current ? ' checked="checked"' : '';
			if ( $echo ) {
				echo $result;
			}
			return $result;
		} );

		// Mock options and transients - return default values
		Functions\when( 'get_option' )->alias( function( $option, $default = false ) {
			return $default;
		} );
		Functions\when( 'get_transient' )->justReturn( false );
		Functions\when( 'set_transient' )->justReturn( true );
		Functions\when( 'delete_transient' )->justReturn( true );

		// Mock HTTP functions - return empty/default values
		Functions\when( 'wp_http_validate_url' )->alias( function( $url ) {
			return filter_var( $url, FILTER_VALIDATE_URL ) !== false ? $url : false;
		} );
		Functions\when( 'wp_remote_get' )->justReturn( [] );
		Functions\when( 'is_wp_error' )->justReturn( false );
		Functions\when( 'wp_remote_retrieve_response_code' )->justReturn( 200 );
		Functions\when( 'wp_remote_retrieve_body' )->justReturn( '' );

		// Mock sprintf - just return format string (tests can override if needed)
		Functions\when( 'sprintf' )->returnArg();

		// Mock WordPress date functions
		Functions\when( 'wp_date' )->alias( function( $format, $timestamp = null ) {
			$timestamp = $timestamp ?? time();
			return date( $format, $timestamp );
		} );
	}
}
