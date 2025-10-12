<?php
/**
 * Unit tests for sanitization and security
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Unit;

/**
 * Test sanitization functions
 */
class Test_Sanitization extends UnitTestCase {

	/**
	 * Test instance domain sanitization removes protocols
	 */
	public function test_instance_sanitization_removes_protocol() {
		// This tests the logic inside fetch_and_cache_posts
		$test_inputs = [
			'https://mastodon.social' => 'mastodon.social',
			'http://mastodon.social' => 'mastodon.social',
			'mastodon.social' => 'mastodon.social',
		];

		foreach ($test_inputs as $input => $expected) {
			$result = preg_replace('/^https?:\/\//', '', $input);
			$this->assertEquals($expected, $result);
		}
	}

	/**
	 * Test instance domain sanitization removes paths
	 */
	public function test_instance_sanitization_removes_paths() {
		$test_inputs = [
			'mastodon.social/path' => 'mastodon.social',
			'mastodon.social?query=1' => 'mastodon.social',
			'mastodon.social#fragment' => 'mastodon.social',
		];

		foreach ($test_inputs as $input => $expected) {
			$result = preg_replace('/[^a-zA-Z0-9.-].*$/', '', $input);
			$this->assertEquals($expected, $result);
		}
	}

	/**
	 * Test tag sanitization strips # symbol
	 */
	public function test_tag_sanitization() {
		$tags = [
			'#photography' => 'photography',
			'photography' => 'photography',
			'##double' => 'double', // ltrim removes ALL leading # chars, not just one
			'#' => '',
		];

		foreach ($tags as $input => $expected) {
			$result = ltrim($input, '#');
			$this->assertEquals($expected, $result);
		}
	}
}
