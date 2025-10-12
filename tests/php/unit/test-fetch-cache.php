<?php
/**
 * Unit tests for fetch_and_cache_posts function
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;
use WP_Error;

/**
 * Test fetch_and_cache_posts functionality
 */
class Test_Fetch_Cache extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Define WordPress constants
		if (!defined('HOUR_IN_SECONDS')) {
			define('HOUR_IN_SECONDS', 3600);
		}
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test cache hit returns cached data
	 */
	public function test_cache_hit_returns_cached_data() {
		$cached_data = [['id' => '123', 'content' => 'Test post']];

		Functions\expect('get_transient')
			->once()
			->andReturn($cached_data);

		// Should not call wp_remote_get if cache hit
		Functions\expect('wp_remote_get')->never();

		require_once __DIR__ . '/../../../mastodon-feed.php';

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

		Functions\expect('get_transient')->once()->andReturn(false);
		Functions\expect('get_option')->andReturn(5);
		Functions\expect('wp_http_validate_url')->andReturnFirstArg();

		Functions\expect('wp_remote_get')
			->once()
			->andReturn(['body' => json_encode($api_response)]);

		Functions\expect('is_wp_error')->once()->andReturn(false);
		Functions\expect('wp_remote_retrieve_response_code')->once()->andReturn(200);
		Functions\expect('wp_remote_retrieve_body')->once()->andReturn(json_encode($api_response));
		Functions\expect('set_transient')->once()->andReturn(true);

		require_once __DIR__ . '/../../../mastodon-feed.php';

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
		Functions\expect('get_transient')->once()->andReturn(false);
		Functions\expect('get_option')->andReturn(5);
		Functions\expect('wp_http_validate_url')->andReturnFirstArg();
		Functions\expect('wp_remote_get')->once()->andReturn(['body' => '']);
		Functions\expect('is_wp_error')->once()->andReturn(false);
		Functions\expect('wp_remote_retrieve_response_code')->once()->andReturn(404);
		Functions\expect('__')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

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
		Functions\expect('get_transient')->once()->andReturn(false);
		Functions\expect('get_option')->andReturn(5);
		Functions\expect('wp_http_validate_url')->andReturnFirstArg();
		Functions\expect('wp_remote_get')->once()->andReturn(['body' => 'invalid json']);
		Functions\expect('is_wp_error')->once()->andReturn(false);
		Functions\expect('wp_remote_retrieve_response_code')->once()->andReturn(200);
		Functions\expect('wp_remote_retrieve_body')->once()->andReturn('invalid json');
		Functions\expect('__')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

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
		Functions\expect('get_transient')->once()->andReturn(false);
		Functions\expect('get_option')->andReturn(5);

		// Should strip https:// and any path/query params
		Functions\expect('wp_http_validate_url')
			->once()
			->with(Mockery::pattern('#^https://mastodon\.social/api#'))
			->andReturnFirstArg();

		Functions\expect('wp_remote_get')->once()->andReturn(['body' => '[]']);
		Functions\expect('is_wp_error')->once()->andReturn(false);
		Functions\expect('wp_remote_retrieve_response_code')->once()->andReturn(200);
		Functions\expect('wp_remote_retrieve_body')->once()->andReturn('[]');
		Functions\expect('set_transient')->once()->andReturn(true);

		require_once __DIR__ . '/../../../mastodon-feed.php';

		\IncludeMastodonFeedPlugin\fetch_and_cache_posts(
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
	}
}
