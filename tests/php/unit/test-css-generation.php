<?php
/**
 * Unit tests for CSS generation
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey\Functions;

/**
 * Test CSS generation functions
 */
class Test_CSS_Generation extends UnitTestCase {

	protected function setUp(): void {
		parent::setUp();

		if (!defined('MASTODON_FEED_STYLE_BG_COLOR')) {
			define('MASTODON_FEED_STYLE_BG_COLOR', 'rgba(219,219,219,0.8)');
		}
		if (!defined('MASTODON_FEED_STYLE_FONT_COLOR')) {
			define('MASTODON_FEED_STYLE_FONT_COLOR', '#000000');
		}
		if (!defined('MASTODON_FEED_STYLE_ACCENT_COLOR')) {
			define('MASTODON_FEED_STYLE_ACCENT_COLOR', '#6364FF');
		}
		if (!defined('MASTODON_FEED_STYLE_ACCENT_FONT_COLOR')) {
			define('MASTODON_FEED_STYLE_ACCENT_FONT_COLOR', '#ffffff');
		}
		if (!defined('MASTODON_FEED_STYLE_BORDER_RADIUS')) {
			define('MASTODON_FEED_STYLE_BORDER_RADIUS', '0.25rem');
		}
	}

	/**
	 * Test CSS generation includes custom properties
	 */
	public function test_css_includes_custom_properties() {
		Functions\when('get_option')->alias(function($key, $default) {
			return $default;
		});
		Functions\when('esc_attr')->returnArg();

		$css = \IncludeMastodonFeedPlugin\get_mastodon_feed_css();

		$this->assertStringContainsString(':root', $css);
		$this->assertStringContainsString('--mastodon-feed-bg', $css);
		$this->assertStringContainsString('--mastodon-feed-font-color', $css);
		$this->assertStringContainsString('--mastodon-feed-accent-color', $css);
	}

	/**
	 * Test CSS includes layout styles
	 */
	public function test_css_includes_layout_styles() {
		Functions\when('get_option')->alias(function($key, $default) {
			return $default;
		});
		Functions\when('esc_attr')->returnArg();

		$css = \IncludeMastodonFeedPlugin\get_mastodon_feed_css();

		$this->assertStringContainsString('.mastodon-feed', $css);
		$this->assertStringContainsString('.mastodon-feed-post', $css);
		$this->assertStringContainsString('.avatar', $css);
		$this->assertStringContainsString('.media', $css);
		$this->assertStringContainsString('.card', $css);
	}

	/**
	 * Test CSS uses var() for theme values
	 */
	public function test_css_uses_css_variables() {
		Functions\when('get_option')->alias(function($key, $default) {
			return $default;
		});
		Functions\when('esc_attr')->returnArg();

		$css = \IncludeMastodonFeedPlugin\get_mastodon_feed_css();

		$this->assertStringContainsString('var(--mastodon-feed-bg)', $css);
		$this->assertStringContainsString('var(--mastodon-feed-accent-color)', $css);
		$this->assertStringContainsString('var(--mastodon-feed-border-radius)', $css);
	}
}
