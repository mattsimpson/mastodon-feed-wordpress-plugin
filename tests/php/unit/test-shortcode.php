<?php
/**
 * Unit tests for shortcode display_feed function
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;
use WP_Error;

/**
 * Test shortcode functionality
 */
class Test_Shortcode extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		if (!defined('MASTODON_FEED_DEFAULT_INSTANCE')) {
			define('MASTODON_FEED_DEFAULT_INSTANCE', 'mastodon.social');
		}
		if (!defined('MASTODON_FEED_LIMIT')) {
			define('MASTODON_FEED_LIMIT', 10);
		}
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test shortcode requires account or tag parameter
	 */
	public function test_shortcode_requires_account_or_tag() {
		Functions\expect('get_option')->andReturn('mastodon.social');
		Functions\expect('esc_html__')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\display_feed([]);

		$this->assertStringContainsString('Error:', $result);
	}

	/**
	 * Test shortcode displays error message
	 */
	public function test_shortcode_displays_wp_error() {
		Functions\expect('get_option')->andReturn('mastodon.social');
		Functions\expect('get_transient')->andReturn(false);
		Functions\expect('wp_http_validate_url')->andReturnFirstArg();
		Functions\expect('wp_remote_get')->andReturn([]);
		Functions\expect('is_wp_error')->andReturn(false);
		Functions\expect('wp_remote_retrieve_response_code')->andReturn(404);
		Functions\expect('__')->andReturnFirstArg();
		Functions\expect('sprintf')->andReturnFirstArg();
		Functions\expect('esc_html')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\display_feed(['account' => 'invalid']);

		$this->assertStringContainsString('mastodon-feed', $result);
	}

	/**
	 * Test shortcode with no posts displays message
	 */
	public function test_shortcode_no_posts_message() {
		Functions\expect('get_option')->andReturn('mastodon.social');
		Functions\expect('get_transient')->andReturn([]);
		Functions\expect('esc_html')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$result = \IncludeMastodonFeedPlugin\display_feed([
			'account' => '123',
			'text-noposts' => 'No posts',
		]);

		$this->assertStringContainsString('No posts', $result);
	}
}
