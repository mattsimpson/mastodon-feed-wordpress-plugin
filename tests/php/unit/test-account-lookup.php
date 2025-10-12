<?php
/**
 * Unit tests for mastodon_feed_do_account_lookup function
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Test account lookup functionality
 */
class Test_Account_Lookup extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		if (!defined('MASTODON_FEED_HTTP_TIMEOUT')) {
			define('MASTODON_FEED_HTTP_TIMEOUT', 5);
		}
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test invalid handle format (missing @)
	 */
	public function test_invalid_handle_format_missing_at() {
		Functions\expect('__')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_do_account_lookup('invalid-handle');

		$this->assertFalse($result['success']);
		$this->assertEquals('invalid_handle', $result['error_code']);
	}

	/**
	 * Test invalid handle format (no domain)
	 */
	public function test_invalid_handle_format_no_domain() {
		Functions\expect('__')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_do_account_lookup('username');

		$this->assertFalse($result['success']);
		$this->assertEquals('invalid_handle', $result['error_code']);
	}

	/**
	 * Test invalid domain (no dot)
	 */
	public function test_invalid_domain_no_dot() {
		Functions\expect('__')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_do_account_lookup('username@localhost');

		$this->assertFalse($result['success']);
		$this->assertEquals('invalid_domain', $result['error_code']);
	}

	/**
	 * Test successful account lookup
	 */
	public function test_successful_lookup() {
		$api_response = [
			'id' => '123456',
			'username' => 'testuser',
			'acct' => 'testuser',
			'display_name' => 'Test User',
			'url' => 'https://mastodon.social/@testuser',
		];

		Functions\expect('get_option')->andReturn(5);
		Functions\expect('wp_remote_get')
			->once()
			->andReturn(['body' => json_encode($api_response)]);
		Functions\expect('is_wp_error')->once()->andReturn(false);
		Functions\expect('wp_remote_retrieve_response_code')->once()->andReturn(200);
		Functions\expect('wp_remote_retrieve_body')->once()->andReturn(json_encode($api_response));

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_do_account_lookup('testuser@mastodon.social');

		$this->assertTrue($result['success']);
		$this->assertEquals('mastodon.social', $result['instance']);
		$this->assertEquals('123456', $result['account_id']);
		$this->assertEquals('Test User', $result['display_name']);
	}

	/**
	 * Test 404 account not found
	 */
	public function test_account_not_found() {
		Functions\expect('get_option')->andReturn(5);
		Functions\expect('wp_remote_get')->once()->andReturn(['body' => '']);
		Functions\expect('is_wp_error')->once()->andReturn(false);
		Functions\expect('wp_remote_retrieve_response_code')->once()->andReturn(404);
		Functions\expect('__')->andReturnFirstArg();
		Functions\expect('sprintf')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_do_account_lookup('nonexistent@mastodon.social');

		$this->assertFalse($result['success']);
		$this->assertEquals('account_not_found', $result['error_code']);
		$this->assertEquals(404, $result['status']);
	}

	/**
	 * Test network timeout error
	 */
	public function test_network_timeout() {
		$wp_error = Mockery::mock('WP_Error');
		$wp_error->shouldReceive('get_error_message')
			->once()
			->andReturn('cURL error 28: Operation timed out');

		Functions\expect('get_option')->andReturn(5);
		Functions\expect('wp_remote_get')->once()->andReturn($wp_error);
		Functions\expect('is_wp_error')->once()->andReturn(true);
		Functions\expect('__')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_do_account_lookup('user@slow-instance.com');

		$this->assertFalse($result['success']);
		$this->assertEquals('connection_timeout', $result['error_code']);
	}

	/**
	 * Test DNS resolution error
	 */
	public function test_dns_resolution_error() {
		$wp_error = Mockery::mock('WP_Error');
		$wp_error->shouldReceive('get_error_message')
			->once()
			->andReturn('cURL error 6: Could not resolve host');

		Functions\expect('get_option')->andReturn(5);
		Functions\expect('wp_remote_get')->once()->andReturn($wp_error);
		Functions\expect('is_wp_error')->once()->andReturn(true);
		Functions\expect('__')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_do_account_lookup('user@nonexistent.invalid');

		$this->assertFalse($result['success']);
		$this->assertEquals('domain_not_found', $result['error_code']);
	}

	/**
	 * Test SSL certificate error
	 */
	public function test_ssl_certificate_error() {
		$wp_error = Mockery::mock('WP_Error');
		$wp_error->shouldReceive('get_error_message')
			->once()
			->andReturn('cURL error 60: SSL certificate problem');

		Functions\expect('get_option')->andReturn(5);
		Functions\expect('wp_remote_get')->once()->andReturn($wp_error);
		Functions\expect('is_wp_error')->once()->andReturn(true);
		Functions\expect('__')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_do_account_lookup('user@bad-ssl.example');

		$this->assertFalse($result['success']);
		$this->assertEquals('ssl_error', $result['error_code']);
	}

	/**
	 * Test rate limit (429) response
	 */
	public function test_rate_limit() {
		Functions\expect('get_option')->andReturn(5);
		Functions\expect('wp_remote_get')->once()->andReturn(['body' => '']);
		Functions\expect('is_wp_error')->once()->andReturn(false);
		Functions\expect('wp_remote_retrieve_response_code')->once()->andReturn(429);
		Functions\expect('__')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_do_account_lookup('user@mastodon.social');

		$this->assertFalse($result['success']);
		$this->assertEquals('rate_limited', $result['error_code']);
		$this->assertEquals(429, $result['status']);
	}

	/**
	 * Test leading @ symbol is stripped
	 */
	public function test_leading_at_symbol_stripped() {
		$api_response = [
			'id' => '123456',
			'username' => 'testuser',
			'acct' => 'testuser',
			'display_name' => 'Test User',
			'url' => 'https://mastodon.social/@testuser',
		];

		Functions\expect('get_option')->andReturn(5);
		Functions\expect('wp_remote_get')->once()->andReturn(['body' => json_encode($api_response)]);
		Functions\expect('is_wp_error')->once()->andReturn(false);
		Functions\expect('wp_remote_retrieve_response_code')->once()->andReturn(200);
		Functions\expect('wp_remote_retrieve_body')->once()->andReturn(json_encode($api_response));

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_do_account_lookup('@testuser@mastodon.social');

		$this->assertTrue($result['success']);
		$this->assertEquals('123456', $result['account_id']);
	}
}
