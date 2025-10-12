<?php
/**
 * Unit tests for render field functions
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

/**
 * Test render functions output correct HTML
 */
class Test_Render_Functions extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Test render_text_field generates proper input
	 */
	public function test_render_text_field() {
		Functions\expect('get_option')->with('test_option', 'default')->andReturn('test_value');
		Functions\expect('esc_attr')->andReturnFirstArg();
		Functions\expect('esc_html')->andReturnFirstArg();
		Functions\expect('wp_kses_post')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		ob_start();
		\IncludeMastodonFeedPlugin\render_text_field([
			'option_name' => 'test_option',
			'default' => 'default',
			'placeholder' => 'Enter text',
		]);
		$output = ob_get_clean();

		$this->assertStringContainsString('type="text"', $output);
		$this->assertStringContainsString('value="test_value"', $output);
		$this->assertStringContainsString('placeholder="Enter text"', $output);
	}

	/**
	 * Test render_checkbox_field generates proper checkbox
	 */
	public function test_render_checkbox_field() {
		Functions\expect('get_option')->with('test_checkbox', '0')->andReturn('1');
		Functions\expect('esc_attr')->andReturnFirstArg();
		Functions\expect('checked')->with(1, '1')->andReturn(' checked="checked"');
		Functions\expect('wp_kses_post')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		ob_start();
		\IncludeMastodonFeedPlugin\render_checkbox_field([
			'option_name' => 'test_checkbox',
			'default' => false,
		]);
		$output = ob_get_clean();

		$this->assertStringContainsString('type="checkbox"', $output);
	}

	/**
	 * Test render_number_field generates proper number input
	 */
	public function test_render_number_field() {
		Functions\expect('get_option')->with('test_number', 10)->andReturn(20);
		Functions\expect('esc_attr')->andReturnFirstArg();
		Functions\expect('esc_html')->andReturnFirstArg();
		Functions\expect('wp_kses_post')->andReturnFirstArg();

		require_once __DIR__ . '/../../../mastodon-feed.php';

		ob_start();
		\IncludeMastodonFeedPlugin\render_number_field([
			'option_name' => 'test_number',
			'default' => 10,
		]);
		$output = ob_get_clean();

		$this->assertStringContainsString('type="number"', $output);
		$this->assertStringContainsString('value="20"', $output);
	}
}
