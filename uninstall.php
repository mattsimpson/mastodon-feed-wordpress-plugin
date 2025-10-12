<?php
/**
 * Uninstall script for Mastodon Feed plugin
 *
 * This file is executed when the plugin is uninstalled via the WordPress admin.
 * It removes all plugin data from the database.
 *
 * @package MastodonFeed
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all plugin options.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable, not global.
$options = array(
	'mastodon_feed_cache_interval',
	'mastodon_feed_http_timeout',
	'mastodon_feed_default_instance',
	'mastodon_feed_limit',
	'mastodon_feed_exclude_boosts',
	'mastodon_feed_exclude_replies',
	'mastodon_feed_only_pinned',
	'mastodon_feed_only_media',
	'mastodon_feed_tagged',
	'mastodon_feed_link_target',
	'mastodon_feed_show_preview_cards',
	'mastodon_feed_style_bg_color',
	'mastodon_feed_style_font_color',
	'mastodon_feed_accent_color',
	'mastodon_feed_accent_font_color',
	'mastodon_feed_style_border_radius',
	'mastodon_feed_show_post_author',
	'mastodon_feed_show_datetime',
	'mastodon_feed_text_no_posts',
	'mastodon_feed_text_boosted',
	'mastodon_feed_text_show_content',
	'mastodon_feed_text_predatetime',
	'mastodon_feed_text_postdatetime',
	'mastodon_feed_text_edited',
	'mastodon_feed_datetime_format',
);

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local variable, not global.
foreach ( $options as $option ) {
	delete_option( $option );
}

// Clear all transient cache data.
global $wpdb;
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_mastodon_feed_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_mastodon_feed_' ) . '%'
	)
);
