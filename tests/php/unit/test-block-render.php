<?php
/**
 * Unit tests for block render callback
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey\Functions;

/**
 * Test block render callback function
 */
class Test_Block_Render extends UnitTestCase {

	protected function setUp(): void {
		parent::setUp();

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

	/**
	 * Test block attributes map to shortcode attributes
	 */
	public function test_block_attributes_map_correctly() {
		Functions\when('get_option')->justReturn('mastodon.social');
		Functions\when('get_transient')->justReturn([]);
		Functions\when('esc_html')->returnArg();
		// __ is already mocked in base class to return first arg

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
		Functions\when('get_option')->justReturn('mastodon.social');
		Functions\when('get_transient')->justReturn([]);
		Functions\when('esc_html')->returnArg();
		// __ is already mocked in base class to return first arg

		$attributes = [
			'account' => '123456',
		];

		$result = \IncludeMastodonFeedPlugin\render_mastodon_feed_block($attributes);

		// Should render with settings from admin
		$this->assertStringContainsString('mastodon-feed', $result);
	}
}
