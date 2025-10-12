<?php
/**
 * Unit tests for block render callback
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * Test block render callback function
 */
class Test_Block_Render extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		if (!defined('MASTODON_FEED_SHOW_PREVIEWCARDS')) {
			define('MASTODON_FEED_SHOW_PREVIEWCARDS', true);
		}
		if (!defined('MASTODON_FEED_SHOW_POST_AUTHOR')) {
			define('MASTODON_FEED_SHOW_POST_AUTHOR', true);
		}
		if (!defined('MASTODON_FEED_SHOW_DATETIME')) {
			define('MASTODON_FEED_SHOW_DATETIME', true);
		}
		if (!defined('MASTODON_FEED_DATETIME_FORMAT')) {
			define('MASTODON_FEED_DATETIME_FORMAT', 'Y-m-d h:i a');
		}
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test block attributes map to shortcode attributes
	 */
	public function test_block_attributes_map_correctly() {
		Functions\expect('get_option')->andReturn('mastodon.social');
		Functions\expect('get_transient')->andReturn([]);
		Functions\expect('esc_html')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$attributes = [
			'instance' => 'fosstodon.org',
			'account' => '123456',
			'limit' => 5,
			'excludeBoosts' => true,
			'excludeReplies' => true,
		];

		$result = \IncludeMastodonFeedPlugin\render_mastodon_feed_block($attributes);

		// Should pass through to display_feed and render
		$this->assertStringContainsString('mastodon-feed', $result);
	}

	/**
	 * Test block uses admin settings as defaults
	 */
	public function test_block_uses_admin_defaults() {
		Functions\expect('get_option')
			->with('mastodon_feed_show_preview_cards', MASTODON_FEED_SHOW_PREVIEWCARDS)
			->andReturn(false);

		Functions\expect('get_option')
			->with('mastodon_feed_show_post_author', MASTODON_FEED_SHOW_POST_AUTHOR)
			->andReturn(true);

		Functions\expect('get_option')->andReturn('mastodon.social');
		Functions\expect('get_transient')->andReturn([]);
		Functions\expect('esc_html')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		$attributes = [
			'account' => '123456',
		];

		$result = \IncludeMastodonFeedPlugin\render_mastodon_feed_block($attributes);

		// Should render with settings from admin
		$this->assertStringContainsString('mastodon-feed', $result);
	}
}
