<?php
/**
 * Integration tests for plugin uninstall
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Integration;

use WP_UnitTestCase;

/**
 * Test plugin uninstall cleanup
 */
class Test_Uninstall extends WP_UnitTestCase {

	/**
	 * Test all plugin options are defined for cleanup
	 */
	public function test_all_options_defined_in_uninstall() {
		$uninstall_file = dirname(dirname(dirname(__DIR__))) . '/uninstall.php';
		$this->assertFileExists($uninstall_file);

		$uninstall_content = file_get_contents($uninstall_file);

		// Check that major options are included
		$required_options = [
			'mastodon_feed_default_instance',
			'mastodon_feed_limit',
			'mastodon_feed_cache_interval',
			'mastodon_feed_style_bg_color',
			'mastodon_feed_text_no_posts',
		];

		foreach ($required_options as $option) {
			$this->assertStringContainsString($option, $uninstall_content);
		}
	}

	/**
	 * Test transient cleanup query is correct
	 */
	public function test_transient_cleanup_query() {
		$uninstall_file = dirname(dirname(dirname(__DIR__))) . '/uninstall.php';
		$uninstall_content = file_get_contents($uninstall_file);

		$this->assertStringContainsString('_transient_mastodon_feed_', $uninstall_content);
		$this->assertStringContainsString('_transient_timeout_mastodon_feed_', $uninstall_content);
	}

	/**
	 * Test options can be deleted
	 */
	public function test_options_can_be_deleted() {
		// Create test options
		update_option('mastodon_feed_default_instance', 'test.social');
		update_option('mastodon_feed_limit', 15);

		$this->assertEquals('test.social', get_option('mastodon_feed_default_instance'));

		// Delete them
		delete_option('mastodon_feed_default_instance');
		delete_option('mastodon_feed_limit');

		// Verify they're gone
		$this->assertFalse(get_option('mastodon_feed_default_instance'));
		$this->assertFalse(get_option('mastodon_feed_limit'));
	}

	/**
	 * Test transients can be deleted
	 */
	public function test_transients_can_be_deleted() {
		// Create test transients
		set_transient('mastodon_feed_cleanup_test', 'data', 3600);

		$this->assertEquals('data', get_transient('mastodon_feed_cleanup_test'));

		// Delete using same logic as uninstall
		global $wpdb;
		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
				$wpdb->esc_like('_transient_mastodon_feed_') . '%',
				$wpdb->esc_like('_transient_timeout_mastodon_feed_') . '%'
			)
		);

		// Verify deletion happened
		$this->assertGreaterThan(0, $deleted, 'Should have deleted at least one row');

		// Clear WordPress object cache to force fresh DB query
		wp_cache_flush();

		$this->assertFalse(get_transient('mastodon_feed_cleanup_test'));
	}
}
