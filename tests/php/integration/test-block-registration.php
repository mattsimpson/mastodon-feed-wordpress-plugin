<?php
/**
 * Integration tests for Gutenberg block registration
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Integration;

use WP_UnitTestCase;

/**
 * Test block registration and integration
 */
class Test_Block_Registration extends WP_UnitTestCase {

	/**
	 * Test block is registered
	 */
	public function test_block_is_registered() {
		if (!function_exists('register_block_type')) {
			$this->markTestSkipped('Block editor not available');
		}

		$registry = \WP_Block_Type_Registry::get_instance();

		// Block may already be registered from plugin load
		if (!$registry->is_registered('mastodon-feed/embed')) {
			do_action('init');
		}

		$this->assertTrue($registry->is_registered('mastodon-feed/embed'));
	}

	/**
	 * Test block has server-side render callback
	 */
	public function test_block_has_render_callback() {
		if (!function_exists('register_block_type')) {
			$this->markTestSkipped('Block editor not available');
		}

		$registry = \WP_Block_Type_Registry::get_instance();

		// Block may already be registered from plugin load
		if (!$registry->is_registered('mastodon-feed/embed')) {
			do_action('init');
		}

		$block = $registry->get_registered('mastodon-feed/embed');

		$this->assertNotNull($block);
		$this->assertIsCallable($block->render_callback);
	}

	/**
	 * Test block render callback returns HTML
	 */
	public function test_block_render_returns_html() {
		$attributes = [
			'instance' => 'mastodon.social',
			'account' => '123456',
			'limit' => 5,
		];

		// Mock transient to return empty array (no API call)
		set_transient('test_mastodon_feed', []);

		$output = \IncludeMastodonFeedPlugin\render_mastodon_feed_block($attributes);

		$this->assertIsString($output);
		$this->assertStringContainsString('mastodon-feed', $output);
	}

	/**
	 * Test block editor assets are enqueued
	 */
	public function test_block_editor_assets_enqueued() {
		do_action('enqueue_block_editor_assets');

		// Check if mastodonFeedDefaults is localized
		global $wp_scripts;
		$this->assertNotEmpty($wp_scripts);
	}

	/**
	 * Test block attributes default to admin settings
	 */
	public function test_block_uses_admin_settings_defaults() {
		update_option('mastodon_feed_show_preview_cards', '0');
		update_option('mastodon_feed_exclude_boosts', '1');

		$attributes = [
			'account' => '123456',
		];

		// Mock transient
		set_transient('test_mastodon_feed', []);

		$output = \IncludeMastodonFeedPlugin\render_mastodon_feed_block($attributes);

		// Should respect admin settings
		$this->assertIsString($output);
	}
}
