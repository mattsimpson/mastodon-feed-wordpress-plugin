<?php
/**
 * PHPStan bootstrap file
 * Defines WordPress constants and functions for static analysis
 */

// Define WordPress constants that PHPStan needs
if (!defined('ABSPATH')) {
	define('ABSPATH', '/tmp/wordpress/');
}

if (!defined('HOUR_IN_SECONDS')) {
	define('HOUR_IN_SECONDS', 3600);
}

// Define plugin namespace constants
if (!defined('MASTODON_FEED_DEFAULT_INSTANCE')) {
	define('MASTODON_FEED_DEFAULT_INSTANCE', 'mastodon.social');
}
