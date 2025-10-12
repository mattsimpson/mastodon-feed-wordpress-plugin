<?php
/**
 * Integration tests for WordPress settings
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Integration;

use WP_UnitTestCase;

/**
 * Test plugin settings integration with WordPress
 */
class Test_Settings extends WP_UnitTestCase {

	/**
	 * Test settings registration
	 */
	public function test_settings_are_registered() {
		global $wp_registered_settings;

		// Only trigger admin_init if settings aren't already registered
		// (they may be registered from previous tests or plugin init)
		if (!isset($wp_registered_settings['mastodon_feed_default_instance'])) {
			// Expected incorrect usage notice from WordPress privacy policy text during testing
			$this->setExpectedIncorrectUsage('wp_add_privacy_policy_content');

			// Suppress headers warning from WordPress during testing
			@do_action('admin_init');
		}

		$this->assertArrayHasKey('mastodon_feed_default_instance', $wp_registered_settings);
		$this->assertArrayHasKey('mastodon_feed_limit', $wp_registered_settings);
		$this->assertArrayHasKey('mastodon_feed_cache_interval', $wp_registered_settings);
	}

	/**
	 * Test option default values
	 */
	public function test_option_defaults() {
		$this->assertEquals('mastodon.social', get_option('mastodon_feed_default_instance', MASTODON_FEED_DEFAULT_INSTANCE));
		$this->assertEquals(10, get_option('mastodon_feed_limit', MASTODON_FEED_LIMIT));
	}

	/**
	 * Test saving and retrieving options
	 */
	public function test_save_and_retrieve_options() {
		update_option('mastodon_feed_default_instance', 'fosstodon.org');
		$this->assertEquals('fosstodon.org', get_option('mastodon_feed_default_instance'));

		update_option('mastodon_feed_limit', 20);
		$this->assertEquals(20, get_option('mastodon_feed_limit'));
	}

	/**
	 * Test boolean options store as strings
	 */
	public function test_boolean_options() {
		update_option('mastodon_feed_exclude_boosts', '1');
		$this->assertEquals('1', get_option('mastodon_feed_exclude_boosts'));

		update_option('mastodon_feed_exclude_replies', '0');
		$this->assertEquals('0', get_option('mastodon_feed_exclude_replies'));
	}

	/**
	 * Test color option validation
	 */
	public function test_color_option_validation() {
		$valid_color = '#FF0000';
		$validated = \IncludeMastodonFeedPlugin\validate_color_option($valid_color);
		$this->assertEquals($valid_color, $validated);

		$invalid_color = 'not-a-color';
		$validated = \IncludeMastodonFeedPlugin\validate_color_option($invalid_color);
		$this->assertEquals('', $validated);
	}

	/**
	 * Test settings can be reset
	 */
	public function test_reset_settings() {
		// Change a setting
		update_option('mastodon_feed_limit', 50);
		$this->assertEquals(50, get_option('mastodon_feed_limit'));

		// Reset to default
		update_option('mastodon_feed_limit', MASTODON_FEED_LIMIT);
		$this->assertEquals(MASTODON_FEED_LIMIT, get_option('mastodon_feed_limit'));
	}
}
