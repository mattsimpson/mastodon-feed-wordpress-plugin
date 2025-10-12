<?php
/**
 * PHPUnit bootstrap file for UNIT tests (with Brain Monkey, no WordPress)
 */

// Require composer autoloader
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
	require dirname(__DIR__) . '/vendor/autoload.php';
}

// Define WP_Error class for tests
if (!class_exists('WP_Error')) {
	class WP_Error {
		private $errors = [];
		private $error_data = [];

		public function __construct($code = '', $message = '', $data = '') {
			if (empty($code)) {
				return;
			}
			$this->errors[$code][] = $message;
			if (!empty($data)) {
				$this->error_data[$code] = $data;
			}
		}

		public function get_error_code() {
			$codes = array_keys($this->errors);
			return $codes[0] ?? '';
		}

		public function get_error_message($code = '') {
			if (empty($code)) {
				$code = $this->get_error_code();
			}
			return $this->errors[$code][0] ?? '';
		}

		public function get_error_messages($code = '') {
			if (empty($code)) {
				return array_reduce($this->errors, 'array_merge', []);
			}
			return $this->errors[$code] ?? [];
		}

		public function get_error_data($code = '') {
			if (empty($code)) {
				$code = $this->get_error_code();
			}
			return $this->error_data[$code] ?? '';
		}

		public function has_errors() {
			return !empty($this->errors);
		}
	}
}

// Define WordPress constants that the plugin needs
if (!defined('HOUR_IN_SECONDS')) {
	define('HOUR_IN_SECONDS', 3600);
}

// Define all plugin constants with defaults
$plugin_constants = [
	'MASTODON_FEED_DEFAULT_INSTANCE' => 'mastodon.social',
	'MASTODON_FEED_LIMIT' => 10,
	'MASTODON_FEED_EXCLUDE_BOOSTS' => false,
	'MASTODON_FEED_EXCLUDE_REPLIES' => false,
	'MASTODON_FEED_ONLY_PINNED' => false,
	'MASTODON_FEED_ONLY_MEDIA' => false,
	'MASTODON_FEED_TAGGED' => false,
	'MASTODON_FEED_LINKTARGET' => '_blank',
	'MASTODON_FEED_SHOW_PREVIEWCARDS' => true,
	'MASTODON_FEED_STYLE_BG_COLOR' => 'rgba(219,219,219,0.8)',
	'MASTODON_FEED_STYLE_FONT_COLOR' => '#000000',
	'MASTODON_FEED_STYLE_ACCENT_COLOR' => '#6364FF',
	'MASTODON_FEED_STYLE_ACCENT_FONT_COLOR' => '#ffffff',
	'MASTODON_FEED_STYLE_BORDER_RADIUS' => '0.25rem',
	'MASTODON_FEED_SHOW_POST_AUTHOR' => true,
	'MASTODON_FEED_SHOW_DATETIME' => true,
	'MASTODON_FEED_TEXT_NO_POSTS' => 'No posts available',
	'MASTODON_FEED_TEXT_BOOSTED' => 'boosted 🚀',
	'MASTODON_FEED_TEXT_SHOW_CONTENT' => 'Show content',
	'MASTODON_FEED_TEXT_PREDATETIME' => 'on',
	'MASTODON_FEED_TEXT_POSTDATETIME' => '',
	'MASTODON_FEED_TEXT_EDITED' => '(edited)',
	'MASTODON_FEED_DATETIME_FORMAT' => 'Y-m-d h:i a',
	'MASTODON_FEED_HTTP_TIMEOUT' => 5,
];

foreach ($plugin_constants as $constant => $value) {
	if (!defined($constant)) {
		define($constant, $value);
	}
}
