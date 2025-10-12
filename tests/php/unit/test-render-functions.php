<?php
/**
 * Unit tests for render field functions
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey\Functions;

/**
 * Test render functions output correct HTML
 */
class Test_Render_Functions extends UnitTestCase {

	/**
	 * Test render_text_field generates proper input
	 */
	public function test_render_text_field() {
		Functions\when('get_option')->justReturn('test_value');
		Functions\when('esc_attr')->returnArg();
		Functions\when('esc_html')->returnArg();
		Functions\when('wp_kses_post')->returnArg();

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
		Functions\when('get_option')->justReturn('1');
		Functions\when('esc_attr')->returnArg();
		Functions\when('checked')->justReturn(' checked="checked"');
		Functions\when('wp_kses_post')->returnArg();

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
		Functions\when('get_option')->justReturn(20);
		Functions\when('esc_attr')->returnArg();
		Functions\when('esc_html')->returnArg();
		Functions\when('wp_kses_post')->returnArg();

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
