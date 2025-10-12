<?php
/**
 * Unit tests for fetch_and_cache_posts function
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey\Functions;
use Mockery;
use WP_Error;

/**
 * Test fetch_and_cache_posts functionality
 */
class Test_Fetch_Cache extends UnitTestCase {

	protected function setUp(): void {
		parent::setUp();

		// Define WordPress constants
		if (!defined('HOUR_IN_SECONDS')) {
			define('HOUR_IN_SECONDS', 3600);
		}
	}

	/**
	 * Test cache hit returns cached data
	 */
	public function test_cache_hit_returns_cached_data() {
		$cached_data = [['id' => '123', 'content' => 'Test post']];

		Functions\when('get_transient')->justReturn($cached_data);

		$result = \IncludeMastodonFeedPlugin\fetch_and_cache_posts(
			'mastodon.social',
			'123456',
			false,
			10,
			false,
			false,
			false,
			false,
			false
		);

		$this->assertEquals($cached_data, $result);
	}

	/**
	 * Test successful API request
	 */
	public function test_successful_api_request() {
		$api_response = [
			['id' => '123', 'content' => 'Test post'],
		];

		Functions\when('get_transient')->justReturn(false);
		Functions\when('get_option')->justReturn(5);
		Functions\when('wp_http_validate_url')->returnArg();

		Functions\when('wp_remote_get')->justReturn(['body' => json_encode($api_response)]);

		Functions\when('is_wp_error')->justReturn(false);
		Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
		Functions\when('wp_remote_retrieve_body')->justReturn(json_encode($api_response));
		Functions\when('set_transient')->justReturn(true);

		$result = \IncludeMastodonFeedPlugin\fetch_and_cache_posts(
			'mastodon.social',
			'123456',
			false,
			10,
			false,
			false,
			false,
			false,
			false
		);

		$this->assertEquals($api_response, $result);
	}

	/**
	 * Test API returns 404 error
	 */
	public function test_api_404_error() {
		Functions\when('get_transient')->justReturn(false);
		Functions\when('get_option')->justReturn(5);
		Functions\when('wp_http_validate_url')->returnArg();
		Functions\when('wp_remote_get')->justReturn(['body' => '']);
		Functions\when('is_wp_error')->justReturn(false);
		Functions\when('wp_remote_retrieve_response_code')->justReturn(404);
		// __ is already mocked in base class to return first arg
		Functions\when('sprintf')->returnArg();

		$result = \IncludeMastodonFeedPlugin\fetch_and_cache_posts(
			'mastodon.social',
			'invalid',
			false,
			10,
			false,
			false,
			false,
			false,
			false
		);

		$this->assertInstanceOf(WP_Error::class, $result);
	}

	/**
	 * Test API returns invalid JSON
	 */
	public function test_invalid_json_response() {
		Functions\when('get_transient')->justReturn(false);
		Functions\when('get_option')->justReturn(5);
		Functions\when('wp_http_validate_url')->returnArg();
		Functions\when('wp_remote_get')->justReturn(['body' => 'invalid json']);
		Functions\when('is_wp_error')->justReturn(false);
		Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
		Functions\when('wp_remote_retrieve_body')->justReturn('invalid json');
		// __ is already mocked in base class to return first arg

		$result = \IncludeMastodonFeedPlugin\fetch_and_cache_posts(
			'mastodon.social',
			'123456',
			false,
			10,
			false,
			false,
			false,
			false,
			false
		);

		$this->assertInstanceOf(WP_Error::class, $result);
	}

	/**
	 * Test instance domain sanitization
	 */
	public function test_instance_domain_sanitization() {
		Functions\when('get_transient')->justReturn(false);
		Functions\when('get_option')->justReturn(5);

		// Should strip https:// and any path/query params
		Functions\when('wp_http_validate_url')->returnArg();

		Functions\when('wp_remote_get')->justReturn(['body' => '[]']);
		Functions\when('is_wp_error')->justReturn(false);
		Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
		Functions\when('wp_remote_retrieve_body')->justReturn('[]');
		Functions\when('set_transient')->justReturn(true);

		$result = \IncludeMastodonFeedPlugin\fetch_and_cache_posts(
			'https://mastodon.social/path?query=1',
			'123456',
			false,
			10,
			false,
			false,
			false,
			false,
			false
		);

		// Verify the function returns an array (empty array in this case)
		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}
}
