<?php
/**
 * Integration tests for admin settings page
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Integration;

use WP_UnitTestCase;

/**
 * Test admin settings page functionality
 */
class Test_Admin_Page extends WP_UnitTestCase {

	protected $admin_user;

	public function setUp(): void {
		parent::setUp();

		$this->admin_user = $this->factory->user->create([
			'role' => 'administrator',
		]);
	}

	/**
	 * Test admin menu is registered
	 */
	public function test_admin_menu_registered() {
		global $submenu;

		wp_set_current_user($this->admin_user);
		set_current_screen('options-general.php');

		do_action('admin_menu');

		$this->assertArrayHasKey('options-general.php', $submenu);

		$found = false;
		foreach ($submenu['options-general.php'] as $item) {
			if ($item[2] === 'mastodon-feed-settings') {
				$found = true;
				break;
			}
		}

		$this->assertTrue($found, 'Mastodon Feed settings menu not found');
	}

	/**
	 * Test cache clearing functionality
	 */
	public function test_clear_cache() {
		// Set some test transients
		set_transient('mastodon_feed_test1', 'data1', 3600);
		set_transient('mastodon_feed_test2', 'data2', 3600);

		// Verify they exist
		$this->assertEquals('data1', get_transient('mastodon_feed_test1'));

		// Clear cache (simulate the function)
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

		// Verify they're gone
		$this->assertFalse(get_transient('mastodon_feed_test1'));
	}

	/**
	 * Test account lookup form handler validates nonce
	 */
	public function test_account_lookup_requires_nonce() {
		wp_set_current_user($this->admin_user);

		$_POST['mastodon_feed_lookup_handle'] = 'test@mastodon.social';
		// Missing nonce

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_handle_lookup();

		$this->assertFalse($result['success']);
	}

	/**
	 * Test account lookup requires handle parameter
	 */
	public function test_account_lookup_requires_handle() {
		wp_set_current_user($this->admin_user);

		$_POST['mastodon_feed_lookup_handle'] = '';
		$_POST['mastodon_feed_lookup_nonce'] = wp_create_nonce('mastodon_feed_lookup_account');

		$result = \IncludeMastodonFeedPlugin\mastodon_feed_handle_lookup();

		$this->assertFalse($result['success']);
	}
}
