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

// Define plugin constants (matching mastodon-feed.php)
if (!defined('MASTODON_FEED_DEFAULT_INSTANCE')) {
	define('MASTODON_FEED_DEFAULT_INSTANCE', 'mastodon.social');
}

if (!defined('MASTODON_FEED_LIMIT')) {
	define('MASTODON_FEED_LIMIT', 20);
}

if (!defined('MASTODON_FEED_EXCLUDE_BOOSTS')) {
	define('MASTODON_FEED_EXCLUDE_BOOSTS', 0);
}

if (!defined('MASTODON_FEED_EXCLUDE_REPLIES')) {
	define('MASTODON_FEED_EXCLUDE_REPLIES', 0);
}

if (!defined('MASTODON_FEED_ONLY_PINNED')) {
	define('MASTODON_FEED_ONLY_PINNED', 0);
}

if (!defined('MASTODON_FEED_ONLY_MEDIA')) {
	define('MASTODON_FEED_ONLY_MEDIA', 0);
}

if (!defined('MASTODON_FEED_TAGGED')) {
	define('MASTODON_FEED_TAGGED', '');
}

if (!defined('MASTODON_FEED_LINKTARGET')) {
	define('MASTODON_FEED_LINKTARGET', '_blank');
}

if (!defined('MASTODON_FEED_SHOW_PREVIEWCARDS')) {
	define('MASTODON_FEED_SHOW_PREVIEWCARDS', 1);
}

if (!defined('MASTODON_FEED_STYLE_BG_COLOR')) {
	define('MASTODON_FEED_STYLE_BG_COLOR', '#282c37');
}

if (!defined('MASTODON_FEED_STYLE_FONT_COLOR')) {
	define('MASTODON_FEED_STYLE_FONT_COLOR', '#ffffff');
}

if (!defined('MASTODON_FEED_STYLE_ACCENT_COLOR')) {
	define('MASTODON_FEED_STYLE_ACCENT_COLOR', '#595aff');
}

if (!defined('MASTODON_FEED_STYLE_ACCENT_FONT_COLOR')) {
	define('MASTODON_FEED_STYLE_ACCENT_FONT_COLOR', '#ffffff');
}

if (!defined('MASTODON_FEED_STYLE_BORDER_RADIUS')) {
	define('MASTODON_FEED_STYLE_BORDER_RADIUS', '8px');
}

if (!defined('MASTODON_FEED_SHOW_POST_AUTHOR')) {
	define('MASTODON_FEED_SHOW_POST_AUTHOR', 1);
}

if (!defined('MASTODON_FEED_SHOW_DATETIME')) {
	define('MASTODON_FEED_SHOW_DATETIME', 1);
}

if (!defined('MASTODON_FEED_TEXT_NO_POSTS')) {
	define('MASTODON_FEED_TEXT_NO_POSTS', 'No posts available.');
}

if (!defined('MASTODON_FEED_TEXT_BOOSTED')) {
	define('MASTODON_FEED_TEXT_BOOSTED', 'boosted');
}

if (!defined('MASTODON_FEED_TEXT_SHOW_CONTENT')) {
	define('MASTODON_FEED_TEXT_SHOW_CONTENT', 'Show content');
}

if (!defined('MASTODON_FEED_TEXT_PREDATETIME')) {
	define('MASTODON_FEED_TEXT_PREDATETIME', '');
}

if (!defined('MASTODON_FEED_TEXT_POSTDATETIME')) {
	define('MASTODON_FEED_TEXT_POSTDATETIME', '');
}

if (!defined('MASTODON_FEED_TEXT_EDITED')) {
	define('MASTODON_FEED_TEXT_EDITED', 'Edited');
}

if (!defined('MASTODON_FEED_DATETIME_FORMAT')) {
	define('MASTODON_FEED_DATETIME_FORMAT', 'Y-m-d h:i a');
}

if (!defined('MASTODON_FEED_HTTP_TIMEOUT')) {
	define('MASTODON_FEED_HTTP_TIMEOUT', 5);
}
