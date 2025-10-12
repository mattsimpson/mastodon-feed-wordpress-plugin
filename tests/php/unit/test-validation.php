<?php
/**
 * Unit tests for validation functions
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey;
use PHPUnit\Framework\TestCase;

/**
 * Test validation functions
 */
class Test_Validation extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test validate_color_option with hex colors
	 */
	public function test_validate_color_option_hex() {
		require_once __DIR__ . '/../../../mastodon-feed.php';

		$this->assertEquals('#fff', \IncludeMastodonFeedPlugin\validate_color_option('#fff'));
		$this->assertEquals('#ffffff', \IncludeMastodonFeedPlugin\validate_color_option('#ffffff'));
		$this->assertEquals('#ffffffff', \IncludeMastodonFeedPlugin\validate_color_option('#ffffffff'));
	}

	/**
	 * Test validate_color_option with rgba
	 */
	public function test_validate_color_option_rgba() {
		require_once __DIR__ . '/../../../mastodon-feed.php';

		$this->assertEquals('rgba(255,255,255,0.8)', \IncludeMastodonFeedPlugin\validate_color_option('rgba(255,255,255,0.8)'));
		$this->assertEquals('rgb(255,255,255)', \IncludeMastodonFeedPlugin\validate_color_option('rgb(255,255,255)'));
	}

	/**
	 * Test validate_color_option with hsla
	 */
	public function test_validate_color_option_hsla() {
		require_once __DIR__ . '/../../../mastodon-feed.php';

		$this->assertEquals('hsla(120,100%,50%,0.5)', \IncludeMastodonFeedPlugin\validate_color_option('hsla(120,100%,50%,0.5)'));
	}

	/**
	 * Test validate_color_option with invalid color
	 */
	public function test_validate_color_option_invalid() {
		require_once __DIR__ . '/../../../mastodon-feed.php';

		$this->assertEquals('', \IncludeMastodonFeedPlugin\validate_color_option('invalid'));
		$this->assertEquals('', \IncludeMastodonFeedPlugin\validate_color_option('#gg'));
	}

	/**
	 * Test validate_link_target with valid targets
	 */
	public function test_validate_link_target_valid() {
		require_once __DIR__ . '/../../../mastodon-feed.php';

		$this->assertEquals('_blank', \IncludeMastodonFeedPlugin\validate_link_target('_blank'));
		$this->assertEquals('_self', \IncludeMastodonFeedPlugin\validate_link_target('_self'));
		$this->assertEquals('_parent', \IncludeMastodonFeedPlugin\validate_link_target('_parent'));
		$this->assertEquals('_top', \IncludeMastodonFeedPlugin\validate_link_target('_top'));
	}

	/**
	 * Test validate_link_target with invalid target returns default
	 */
	public function test_validate_link_target_invalid() {
		require_once __DIR__ . '/../../../mastodon-feed.php';

		$this->assertEquals('_blank', \IncludeMastodonFeedPlugin\validate_link_target('invalid'));
		$this->assertEquals('_blank', \IncludeMastodonFeedPlugin\validate_link_target(''));
	}

	/**
	 * Test validate_border_radius with valid values
	 */
	public function test_validate_border_radius_valid() {
		require_once __DIR__ . '/../../../mastodon-feed.php';

		$this->assertEquals('0', \IncludeMastodonFeedPlugin\validate_border_radius('0'));
		$this->assertEquals('10px', \IncludeMastodonFeedPlugin\validate_border_radius('10px'));
		$this->assertEquals('0.25rem', \IncludeMastodonFeedPlugin\validate_border_radius('0.25rem'));
		$this->assertEquals('50%', \IncludeMastodonFeedPlugin\validate_border_radius('50%'));
	}

	/**
	 * Test validate_border_radius with invalid values returns default
	 */
	public function test_validate_border_radius_invalid() {
		require_once __DIR__ . '/../../../mastodon-feed.php';

		$this->assertEquals('0.25rem', \IncludeMastodonFeedPlugin\validate_border_radius('invalid'));
		$this->assertEquals('0.25rem', \IncludeMastodonFeedPlugin\validate_border_radius(''));
	}

	/**
	 * Test validate_boolean_option
	 */
	public function test_validate_boolean_option() {
		require_once __DIR__ . '/../../../mastodon-feed.php';

		$this->assertEquals('1', \IncludeMastodonFeedPlugin\validate_boolean_option('1'));
		$this->assertEquals('0', \IncludeMastodonFeedPlugin\validate_boolean_option('0'));
		$this->assertEquals('0', \IncludeMastodonFeedPlugin\validate_boolean_option('invalid'));
	}

	/**
	 * Test sanitize_tag_field strips # symbol
	 */
	public function test_sanitize_tag_field() {
		require_once __DIR__ . '/../../../mastodon-feed.php';

		Brain\Monkey\Functions\expect('sanitize_text_field')->andReturnFirstArg();

		$this->assertEquals('photography', \IncludeMastodonFeedPlugin\sanitize_tag_field('#photography'));
		$this->assertEquals('travel', \IncludeMastodonFeedPlugin\sanitize_tag_field('travel'));
		$this->assertEquals('', \IncludeMastodonFeedPlugin\sanitize_tag_field('#'));
	}
}
