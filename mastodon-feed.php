<?php
/*
	Plugin Name: Mastodon Feed
	Plugin URI: https://github.com/mattsimpson/mastodon-feed-wordpress-plugin
	Description: Using the [mastodon-feed] shortcode, this plugin will display specified Mastodon account feeds on your WordPress website.
	Version: 1.0.0
	Author: Matt Simpson
	Author URI: https://mattsimpson.ca
	License: MIT
	License URI: https://directory.fsf.org/wiki/License:Expat
	Text Domain: mastodon-feed
*/

namespace IncludeMastodonFeedPlugin;

use WP_Error;

// Set defaults.
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Temporary variable for defining constants.
$constants = array(
	array(
		'key'   => 'MASTODON_FEED_DEFAULT_INSTANCE',
		'value' => 'mastodon.social',
	),
	array(
		'key'   => 'MASTODON_FEED_LIMIT',
		'value' => 10,
	),
	array(
		'key'   => 'MASTODON_FEED_EXCLUDE_BOOSTS',
		'value' => false,
	),
	array(
		'key'   => 'MASTODON_FEED_EXCLUDE_REPLIES',
		'value' => false,
	),
	array(
		'key'   => 'MASTODON_FEED_ONLY_PINNED',
		'value' => false,
	),
	array(
		'key'   => 'MASTODON_FEED_ONLY_MEDIA',
		'value' => false,
	),
	array(
		'key'   => 'MASTODON_FEED_TAGGED',
		'value' => false,
	),
	array(
		'key'   => 'MASTODON_FEED_LINKTARGET',
		'value' => '_blank',
	),
	array(
		'key'   => 'MASTODON_FEED_SHOW_PREVIEWCARDS',
		'value' => true,
	),

	// Set styles.
	array(
		'key'   => 'MASTODON_FEED_STYLE_BG_COLOR',
		'value' => 'rgba(219,219,219,0.8)',
	),
	array(
		'key'   => 'MASTODON_FEED_STYLE_FONT_COLOR',
		'value' => '#000000',
	),
	array(
		'key'   => 'MASTODON_FEED_STYLE_ACCENT_COLOR',
		'value' => '#6364FF',
	),
	array(
		'key'   => 'MASTODON_FEED_STYLE_ACCENT_FONT_COLOR',
		'value' => '#ffffff',
	),
	array(
		'key'   => 'MASTODON_FEED_STYLE_BORDER_RADIUS',
		'value' => '0.25rem',
	),
	array(
		'key'   => 'MASTODON_FEED_SHOW_POST_AUTHOR',
		'value' => true,
	),
	array(
		'key'   => 'MASTODON_FEED_SHOW_DATETIME',
		'value' => true,
	),

	// Set text and localization.
	array(
		'key'   => 'MASTODON_FEED_TEXT_NO_POSTS',
		'value' => 'No posts available',
	),
	array(
		'key'   => 'MASTODON_FEED_TEXT_BOOSTED',
		'value' => 'boosted 🚀',
	),
	array(
		'key'   => 'MASTODON_FEED_TEXT_SHOW_CONTENT',
		'value' => 'Show content',
	),
	array(
		'key'   => 'MASTODON_FEED_TEXT_PREDATETIME',
		'value' => 'on',
	),
	array(
		'key'   => 'MASTODON_FEED_TEXT_POSTDATETIME',
		'value' => '',
	),
	array(
		'key'   => 'MASTODON_FEED_TEXT_EDITED',
		'value' => '(edited)',
	),
	array(
		'key'   => 'MASTODON_FEED_DATETIME_FORMAT',
		'value' => 'Y-m-d h:i a',
	),
	array(
		'key'   => 'MASTODON_FEED_HTTP_TIMEOUT',
		'value' => 5,
	),
);
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound,WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound -- Loop variable for defining constants.
foreach ( $constants as $constant ) {
	if ( ! defined( $constant['key'] ) ) {
		define( $constant['key'], $constant['value'] );
	}
}
unset( $constants );

// Track if shortcode or block is used on the page.
$mastodon_feed_shortcode_used = false;

// Cache for widget detection result (null = not checked yet).
$mastodon_feed_widgets_checked = null;

/**
 * Check if the shortcode or block is present in any active widgets.
 *
 * Scans all active sidebars and widgets for the mastodon-feed shortcode or block.
 * Results are cached globally to avoid rescanning on the same page load.
 *
 * @return bool True if shortcode/block found in widgets, false otherwise.
 */
function check_widgets_for_feed() {
	global $mastodon_feed_widgets_checked;

	// Return cached result if already checked.
	if ( null !== $mastodon_feed_widgets_checked ) {
		return $mastodon_feed_widgets_checked;
	}

	// Default to false.
	$mastodon_feed_widgets_checked = false;

	$sidebars_widgets = wp_get_sidebars_widgets();
	if ( ! is_array( $sidebars_widgets ) ) {
		return false;
	}

	$widget_types = array( 'text', 'custom_html', 'block' );

	foreach ( $sidebars_widgets as $sidebar => $widgets ) {
		if ( 'wp_inactive_widgets' === $sidebar || ! is_array( $widgets ) ) {
			continue;
		}

		foreach ( $widgets as $widget ) {
			foreach ( $widget_types as $type ) {
				if ( strpos( $widget, $type . '-' ) === 0 ) {
					$widget_id      = str_replace( $type . '-', '', $widget );
					$widget_options = get_option( 'widget_' . $type );

					if ( is_array( $widget_options ) && isset( $widget_options[ $widget_id ] ) ) {
						$widget_data = $widget_options[ $widget_id ];

						$content_fields = array( 'text', 'content', 'title' );
						foreach ( $content_fields as $field ) {
							if ( isset( $widget_data[ $field ] ) && is_string( $widget_data[ $field ] ) ) {
								// Check for shortcode.
								if ( has_shortcode( $widget_data[ $field ], 'mastodon-feed' ) ) {
									$mastodon_feed_widgets_checked = true;
									return true;
								}
								// Check for Gutenberg block (used in block widgets).
								if ( has_block( 'mastodon-feed/embed', $widget_data[ $field ] ) ) {
									$mastodon_feed_widgets_checked = true;
									return true;
								}
							}
						}
					}
					break;
				}
			}
		}
	}

	return false;
}

/**
 * Check if the shortcode or block is used on the current page.
 *
 * Sets global $mastodon_feed_shortcode_used flag for conditional CSS/JS loading.
 * Checks post content and Gutenberg blocks. Widget checking is deferred to
 * init_styles() and enqueue_frontend_scripts() as widgets may not be available yet.
 */
function check_for_shortcode() {
	global $post, $mastodon_feed_shortcode_used;

	// Check post content and blocks.
	if ( is_a( $post, 'WP_Post' ) ) {
		// Check for shortcode.
		if ( has_shortcode( $post->post_content, 'mastodon-feed' ) ) {
			$mastodon_feed_shortcode_used = true;
			return;
		}

		// Check for Gutenberg block.
		if ( has_block( 'mastodon-feed/embed', $post ) ) {
			$mastodon_feed_shortcode_used = true;
			return;
		}
	}

	// Note: Widget checking is deferred to init_styles() and enqueue_frontend_scripts()
	// as widgets may not be fully available at this hook timing.
}

add_action( 'wp', __NAMESPACE__ . '\check_for_shortcode' );

/**
 * Generate CSS for Mastodon feed (used by both frontend and block editor).
 *
 * @return string CSS styles with custom properties based on settings.
 */
function get_mastodon_feed_css() {
	ob_start();
	?>
	:root {
	--mastodon-feed-bg: <?php echo esc_attr( get_option( 'mastodon_feed_style_bg_color', MASTODON_FEED_STYLE_BG_COLOR ) ); ?>;
	--mastodon-feed-font-color: <?php echo esc_attr( get_option( 'mastodon_feed_style_font_color', MASTODON_FEED_STYLE_FONT_COLOR ) ); ?>;
	--mastodon-feed-accent-color: <?php echo esc_attr( get_option( 'mastodon_feed_accent_color', MASTODON_FEED_STYLE_ACCENT_COLOR ) ); ?>;
	--mastodon-feed-accent-font-color: <?php echo esc_attr( get_option( 'mastodon_feed_accent_font_color', MASTODON_FEED_STYLE_ACCENT_FONT_COLOR ) ); ?>;
	--mastodon-feed-border-radius: <?php echo esc_attr( get_option( 'mastodon_feed_style_border_radius', MASTODON_FEED_STYLE_BORDER_RADIUS ) ); ?>;
	}

	.mastodon-feed .mastodon-feed-post {
	margin: 0.75rem 0;
	border-radius: var(--mastodon-feed-border-radius);
	padding: 0.75rem;
	background: var(--mastodon-feed-bg);
	color: var(--mastodon-feed-font-color);
	}

	.mastodon-feed .mastodon-feed-post a {
	color: var(--mastodon-feed-accent-color);
	text-decoration: none;
	word-wrap: break-word;
	transition: color 0.2s ease, text-decoration 0.2s ease;
	}

	.mastodon-feed .mastodon-feed-post a:hover,
	.mastodon-feed .mastodon-feed-post a:focus-visible {
	text-decoration: underline;
	}

	.mastodon-feed img.avatar {
	display: inline-block;
	height: 1.5rem;
	border-radius: var(--mastodon-feed-border-radius);
	vertical-align: top;
	}

	.mastodon-feed .account {
	align-items: center;
	flex-wrap: nowrap;
	gap: 0.25rem;
	}

	.mastodon-feed .account a {
	display: inline-block;
	}

	.mastodon-feed .account .booster {
	margin-left: auto;
	font-style: italic;
	}

	.mastodon-feed .boosted .account > a:first-child,
	.mastodon-feed .content-warning a {
	border-radius: var(--mastodon-feed-border-radius);
	padding: 0.3rem 0.3rem 0.2rem 0.3rem;
	background: var(--mastodon-feed-accent-color);
	color: var(--mastodon-feed-accent-font-color);
	transition: background 0.2s ease, color 0.2s ease;
	}

	.mastodon-feed .boosted .account > a:first-child:hover,
	.mastodon-feed .boosted .account > a:first-child:focus-visible,
	.mastodon-feed .content-warning a:hover,
	.mastodon-feed .content-warning a:focus-visible {
	background: var(--mastodon-feed-accent-font-color);
	color: var(--mastodon-feed-accent-color);
	text-decoration: none;
	}

	.mastodon-feed .content-wrapper.boosted {
	margin: 0.5rem 0;
	padding: 0.35rem;
	background: var(--mastodon-feed-bg);
	border-radius: var(--mastodon-feed-border-radius);
	}

	.mastodon-feed .content-warning {
	text-align: center;
	margin: 1rem;
	padding: 1rem;
	}

	.mastodon-feed .content-warning .title {
	font-weight: bold;
	}

	.mastodon-feed img.emoji {
	height: 1rem;
	}

	.mastodon-feed .mastodon-feed-content .invisible {
	display: none;
	}

	.mastodon-feed .mastodon-feed-content > p {
	margin: 0.5rem 0;
	}

	.mastodon-feed .media {
	display: flex;
	justify-content: space-around;
	align-items: center;
	flex-wrap: wrap;
	gap: 0.5rem;
	margin: 1rem;
	}

	.mastodon-feed .media > div {
	flex-basis: calc(50% - 0.5rem);
	flex-grow: 1;
	}

	.mastodon-feed .media > .image {
	font-size: 0.8rem;
	font-weight: bold;
	text-align: center;
	}

	.mastodon-feed .media > .image a {
	border-radius: var(--mastodon-feed-border-radius);
	display: block;
	position: relative;
	background-size: cover;
	background-position: center;
	transition: filter 0.3s ease;
	}

	.mastodon-feed .media > .image a::before {
	content: '';
	display: block;
	padding-top: 61.8%;
	}

	@supports (aspect-ratio: 1.618) {
		.mastodon-feed .media > .image a {
			aspect-ratio: 1.618;
		}
		.mastodon-feed .media > .image a::before {
			display: none;
		}
	}

	.mastodon-feed .media > .image a:hover,
	.mastodon-feed .media > .image a:focus-visible {
	filter: contrast(110%) brightness(130%) saturate(130%);
	}

	.mastodon-feed .media > .image a img {
	width: 100%;
	}

	.mastodon-feed .media > .gifv video {
	max-width: 100%;
	}

	.mastodon-feed .card {
	border-radius: var(--mastodon-feed-border-radius);
	margin: 1rem 0.5rem;
	}

	.mastodon-feed .card iframe {
	border-radius: var(--mastodon-feed-border-radius);
	width: 100%;
	min-height: 200px;
	}

	@supports (aspect-ratio: 2 / 1.25) {
		.mastodon-feed .card iframe {
			aspect-ratio: 2 / 1.25;
			height: 100%;
		}
	}

	.mastodon-feed .card a {
	border-radius: var(--mastodon-feed-border-radius);
	display: block;
	text-decoration: none;
	color: inherit;
	transition: background 0.2s ease, color 0.2s ease;
	}

	.mastodon-feed .card a:hover,
	.mastodon-feed .card a:focus-visible {
	text-decoration: none;
	background: var(--mastodon-feed-accent-color);
	color: var(--mastodon-feed-accent-font-color);
	}

	.mastodon-feed .card .meta {
	font-size: 0.8rem;
	padding: 1rem;
	}

	.mastodon-feed .card .image {
	padding: 1rem 1rem 0 1rem;
	text-align: center;
	}

	.mastodon-feed .card .image img {
	max-width: 75%;
	}

	.mastodon-feed .card .title {
	font-weight: bold;
	}
	<?php
	return ob_get_clean();
}

/**
 * Initialize and output CSS styles for Mastodon feed in page head.
 *
 * Only outputs styles if the shortcode or block is detected on the current page.
 * Performs a final check for widgets if not already detected, since widgets may
 * not be available during the earlier 'wp' hook.
 */
function init_styles() {
	global $mastodon_feed_shortcode_used;

	// If not already detected, check widgets (cached to avoid duplicate scans).
	if ( ! $mastodon_feed_shortcode_used && check_widgets_for_feed() ) {
		$mastodon_feed_shortcode_used = true;
	}

	// Only output CSS if shortcode is present.
	if ( ! $mastodon_feed_shortcode_used ) {
		return;
	}

	echo '<style>' . get_mastodon_feed_css() . '</style>';
}

add_action( 'wp_head', __NAMESPACE__ . '\init_styles', 7 );

/**
 * Enqueue frontend JavaScript for Mastodon feed functionality.
 *
 * Only loads scripts if the shortcode or block is detected on the current page.
 * Performs a final check for widgets if not already detected, since widgets may
 * not be available during the earlier 'wp' hook.
 */
function enqueue_frontend_scripts() {
	global $mastodon_feed_shortcode_used;

	// If not already detected, check widgets (cached to avoid duplicate scans).
	if ( ! $mastodon_feed_shortcode_used && check_widgets_for_feed() ) {
		$mastodon_feed_shortcode_used = true;
	}

	// Only enqueue if shortcode is present.
	if ( ! $mastodon_feed_shortcode_used ) {
		return;
	}

	// Get plugin version for cache busting.
	$plugin_data    = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
	$plugin_version = $plugin_data['Version'];

	wp_enqueue_script(
		'mastodon-feed-frontend',
		plugins_url( 'assets/js/mastodon-feed.js', __FILE__ ),
		array( 'jquery' ),
		$plugin_version,
		true
	);
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_frontend_scripts' );

/**
 * Enqueue styles and settings for the Gutenberg block editor.
 *
 * Adds inline CSS and passes admin settings as defaults to the block editor.
 */
function enqueue_block_editor_assets() {
	// Add inline styles for the block editor.
	$custom_css = generate_inline_styles();

	wp_add_inline_style( 'wp-edit-blocks', $custom_css );

	// Pass admin settings as defaults to the block editor.
	// This ensures block editor toggles reflect current admin settings.
	wp_localize_script(
		'mastodon-feed-embed-editor-script',
		'mastodonFeedDefaults',
		array(
			'showPreviewCards' => (bool) get_option( 'mastodon_feed_show_preview_cards', MASTODON_FEED_SHOW_PREVIEWCARDS ),
			'showPostAuthor'   => (bool) get_option( 'mastodon_feed_show_post_author', MASTODON_FEED_SHOW_POST_AUTHOR ),
			'showDateTime'     => (bool) get_option( 'mastodon_feed_show_datetime', MASTODON_FEED_SHOW_DATETIME ),
			'excludeBoosts'    => (bool) get_option( 'mastodon_feed_exclude_boosts', MASTODON_FEED_EXCLUDE_BOOSTS ),
			'excludeReplies'   => (bool) get_option( 'mastodon_feed_exclude_replies', MASTODON_FEED_EXCLUDE_REPLIES ),
			'onlyPinned'       => (bool) get_option( 'mastodon_feed_only_pinned', MASTODON_FEED_ONLY_PINNED ),
			'onlyMedia'        => (bool) get_option( 'mastodon_feed_only_media', MASTODON_FEED_ONLY_MEDIA ),
		)
	);
}

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );

/**
 * Generate inline CSS styles for the feed.
 *
 * @return string CSS styles with custom properties.
 */
function generate_inline_styles() {
	return get_mastodon_feed_css();
}

/**
 * Fetch posts from Mastodon API with caching support.
 *
 * Retrieves posts from a Mastodon instance via API and caches them using WordPress transients.
 * Supports filtering by account, tag, and various post attributes.
 *
 * @param string $instance        Mastodon instance domain (e.g., 'mastodon.social').
 * @param string $account         Account ID to fetch posts from, or false for tag-based feeds.
 * @param string $tag             Tag to filter posts by, or false for account-based feeds.
 * @param int    $limit           Maximum number of posts to retrieve.
 * @param bool   $exclude_boosts  Whether to exclude boosted/reblogged posts.
 * @param bool   $exclude_replies Whether to exclude reply posts.
 * @param bool   $only_pinned     Whether to only show pinned posts.
 * @param bool   $only_media      Whether to only show posts with media attachments.
 * @param string $tagged          Additional tag filter for posts.
 *
 * @return array|WP_Error Array of posts on success, WP_Error on failure.
 */
function fetch_and_cache_posts( $instance, $account, $tag, $limit, $exclude_boosts, $exclude_replies, $only_pinned, $only_media, $tagged ) {

	$cache_key   = 'mastodon_feed_' . md5( wp_json_encode( func_get_args() ) );
	$cached_data = get_transient( $cache_key );

	if ( ! empty( $cached_data ) ) {
		return $cached_data;
	}

	// Sanitize instance domain (remove any protocols, paths, or special chars).
	$instance = preg_replace( '/^https?:\/\//', '', $instance );
	$instance = preg_replace( '/[^a-zA-Z0-9.-].*$/', '', $instance );

	$api_url = 'https://' . $instance;

	if ( $account ) {
		$api_url .= '/api/v1/accounts/' . urlencode( $account ) . '/statuses';
	} elseif ( $tag ) {
		$api_url .= '/api/v1/timelines/tag/' . urlencode( $tag );
	}

	$get_params = array();
	if ( $limit > 0 ) {
		$get_params[] = 'limit=' . intval( $limit );
	}
	if ( $exclude_boosts ) {
		$get_params[] = 'exclude_reblogs=true';
	}
	if ( $exclude_replies ) {
		$get_params[] = 'exclude_replies=true';
	}
	if ( $only_pinned ) {
		$get_params[] = 'pinned=true';
	}
	if ( $only_media ) {
		$get_params[] = 'only_media=true';
	}
	if ( $tagged ) {
		$get_params[] = 'tagged=' . urlencode( $tagged );
	}
	if ( ! empty( $get_params ) ) {
		$api_url .= '?' . implode( '&', $get_params );
	}

	$api_url  = wp_http_validate_url( $api_url );
	$timeout  = get_option( 'mastodon_feed_http_timeout', MASTODON_FEED_HTTP_TIMEOUT );
	$response = wp_remote_get( $api_url, array( 'timeout' => $timeout ) );
	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'mastodon_fetch_error', __( 'Failed to fetch data from the Mastodon API.', 'mastodon-feed' ) );
	}

	// Check HTTP status code.
	$status_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $status_code ) {
		return new WP_Error(
			'mastodon_http_error',
			sprintf(
						/* translators: %d: HTTP status code (e.g. 404, 500) */
				__( 'Mastodon API returned HTTP status %d. Please check your instance URL and account ID.', 'mastodon-feed' ),
				$status_code
			)
		);
	}

	$data  = wp_remote_retrieve_body( $response );
	$posts = json_decode( $data, true );

	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return new WP_Error( 'mastodon_json_error', __( 'Failed to parse the JSON data returned from the Mastodon API.', 'mastodon-feed' ) );
	}

	// Validate that response is an array.
	if ( ! is_array( $posts ) ) {
		return new WP_Error( 'mastodon_invalid_response', __( 'Mastodon API returned invalid data format.', 'mastodon-feed' ) );
	}

	$cache_interval = get_option( 'mastodon_feed_cache_interval', HOUR_IN_SECONDS );
	set_transient( $cache_key, $posts, $cache_interval );

	return $posts;
}

/**
 * Render the Mastodon Feed settings page in WordPress admin.
 *
 * Displays tabbed interface for plugin configuration including general settings,
 * feed filters, display options, styling, text customization, account lookup, and cache management.
 */
function mastodon_feed_settings_page() {
	// Handle reset settings action.
	if ( isset( $_POST['mastodon_feed_reset_settings'] ) ) {
		mastodon_feed_reset_settings();
	}

	// Handle clear cache action.
	if ( isset( $_POST['mastodon_feed_clear_cache'] ) ) {
		mastodon_feed_clear_cache();
	}

	// Handle account lookup.
	$lookup_result = null;
	if ( isset( $_POST['mastodon_feed_lookup_handle'] ) ) {
		$lookup_result = mastodon_feed_handle_lookup();
	}

	// Enqueue color picker styles and scripts for styling tab.
	wp_enqueue_style( 'wp-color-picker' );

	// Get plugin version for cache busting.
	$plugin_data    = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
	$plugin_version = $plugin_data['Version'];

	// Enqueue the alpha-enabled color picker.
	wp_enqueue_script(
		'wp-color-picker-alpha',
		plugins_url( 'assets/js/wp-color-picker-alpha.min.js', __FILE__ ),
		array( 'jquery', 'wp-color-picker' ),
		'3.0.4',
		true
	);

	wp_enqueue_script(
		'mastodon-feed-admin',
		plugins_url( 'assets/js/mastodon-feed.js', __FILE__ ),
		array( 'wp-color-picker-alpha' ),
		$plugin_version,
		true
	);

	// Define tabs structure.
	$tabs = array(
		'general'        => __( 'General Settings', 'mastodon-feed' ),
		'filters'        => __( 'Feed Filters', 'mastodon-feed' ),
		'display'        => __( 'Display Options', 'mastodon-feed' ),
		'styling'        => __( 'Styling', 'mastodon-feed' ),
		'text'           => __( 'Text Customization', 'mastodon-feed' ),
		'account-lookup' => __( 'Account Lookup', 'mastodon-feed' ),
		'cache'          => __( 'Cache Management', 'mastodon-feed' ),
	);

	// Get active tab from URL parameter.
	$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
	if ( ! array_key_exists( $active_tab, $tabs ) ) {
		$active_tab = 'general';
	}

	// Set up args for templates.
	$template_args = array(
		'tabs'          => $tabs,
		'active_tab'    => $active_tab,
		$active_tab     => 'nav-tab-active',
		'lookup_result' => $lookup_result,
		'cache_cleared' => isset( $_POST['mastodon_feed_clear_cache'] ) && ! empty( $_POST['mastodon_feed_clear_cache'] ),
	);

	?>
	<style>
		.mastodon-feed-settings {
			margin: 0;
			padding: 0;
		}

		.mastodon-feed-settings-header {
			background: #fff;
			border-bottom: 1px solid #ccd0d4;
			margin: 0 0 0 -20px;
			padding: 12px 32px 0;
		}

		.mastodon-feed-settings-title-section {
			padding-bottom: 0;
		}

		.mastodon-feed-settings-title-section h1 {
			font-size: 23px;
			font-weight: 400;
			margin: 0;
			padding: 9px 0 4px;
			line-height: 1.3;
		}

		.mastodon-feed-settings-header .nav-tab-wrapper {
			border-bottom: 0;
			margin: 0;
			padding-top: 8px;
		}

		.mastodon-feed-settings-header .nav-tab {
			border-bottom: 4px solid transparent;
			box-shadow: none;
			background: transparent;
			border-left: 0;
			border-right: 0;
			border-top: 0;
			margin: 0 16px 0 0;
			padding: 8px 12px 12px;
		}

		.mastodon-feed-settings-header .nav-tab:hover {
			background: transparent;
			border-bottom-color: #646970;
		}

		.mastodon-feed-settings-header .nav-tab.nav-tab-active {
			background: transparent;
			border-bottom-color: #3582c4;
			color: #3582c4;
		}

		.mastodon-feed-settings-page {
			max-width: 1000px;
			padding: 24px;
			margin: 24px 0 0 0;
		}

		.mastodon-feed-settings .form-table {
			margin-top: 0;
		}

		.mastodon-feed-settings .form-table th {
			width: 300px;
			padding-top: 20px;
		}

		.mastodon-feed-settings .form-table td {
			padding-top: 20px;
		}
	</style>
	<div class="wrap mastodon-feed-settings">
		<?php settings_errors( 'mastodon_feed_messages' ); ?>

		<?php
		// Load the admin header template with tabs.
		load_template( __DIR__ . '/templates/admin-header.php', false, $template_args );
		?>

		<?php
		// Load the appropriate tab content template based on active tab.
		$template_files = array(
			'general'        => __DIR__ . '/templates/general-settings.php',
			'filters'        => __DIR__ . '/templates/feed-filters.php',
			'display'        => __DIR__ . '/templates/display-options.php',
			'styling'        => __DIR__ . '/templates/styling.php',
			'text'           => __DIR__ . '/templates/text-customization.php',
			'account-lookup' => __DIR__ . '/templates/account-lookup.php',
			'cache'          => __DIR__ . '/templates/cache-management.php',
		);

		if ( isset( $template_files[ $active_tab ] ) && file_exists( $template_files[ $active_tab ] ) ) {
			load_template( $template_files[ $active_tab ], false, $template_args );
		}
		?>
	</div>
	<?php
}

/**
 * Reset all plugin settings to their default values.
 *
 * Verifies nonce and permissions before resetting all plugin options to their
 * default constant values defined at the top of the file.
 */
function mastodon_feed_reset_settings() {
	if ( ! isset( $_POST['mastodon_feed_reset_settings_nonce'] ) ||
			! wp_verify_nonce( $_POST['mastodon_feed_reset_settings_nonce'], 'mastodon_feed_reset_settings' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Reset all plugin options to their default constant values.
	update_option( 'mastodon_feed_default_instance', MASTODON_FEED_DEFAULT_INSTANCE );
	update_option( 'mastodon_feed_limit', MASTODON_FEED_LIMIT );
	update_option( 'mastodon_feed_cache_interval', HOUR_IN_SECONDS );
	update_option( 'mastodon_feed_http_timeout', MASTODON_FEED_HTTP_TIMEOUT );
	update_option( 'mastodon_feed_exclude_boosts', MASTODON_FEED_EXCLUDE_BOOSTS ? '1' : '0' );
	update_option( 'mastodon_feed_exclude_replies', MASTODON_FEED_EXCLUDE_REPLIES ? '1' : '0' );
	update_option( 'mastodon_feed_only_pinned', MASTODON_FEED_ONLY_PINNED ? '1' : '0' );
	update_option( 'mastodon_feed_only_media', MASTODON_FEED_ONLY_MEDIA ? '1' : '0' );
	update_option( 'mastodon_feed_tagged', MASTODON_FEED_TAGGED );
	update_option( 'mastodon_feed_link_target', MASTODON_FEED_LINKTARGET );
	update_option( 'mastodon_feed_show_preview_cards', MASTODON_FEED_SHOW_PREVIEWCARDS ? '1' : '0' );
	update_option( 'mastodon_feed_style_bg_color', MASTODON_FEED_STYLE_BG_COLOR );
	update_option( 'mastodon_feed_style_font_color', MASTODON_FEED_STYLE_FONT_COLOR );
	update_option( 'mastodon_feed_accent_color', MASTODON_FEED_STYLE_ACCENT_COLOR );
	update_option( 'mastodon_feed_accent_font_color', MASTODON_FEED_STYLE_ACCENT_FONT_COLOR );
	update_option( 'mastodon_feed_style_border_radius', MASTODON_FEED_STYLE_BORDER_RADIUS );
	update_option( 'mastodon_feed_show_post_author', MASTODON_FEED_SHOW_POST_AUTHOR ? '1' : '0' );
	update_option( 'mastodon_feed_show_datetime', MASTODON_FEED_SHOW_DATETIME ? '1' : '0' );
	update_option( 'mastodon_feed_text_no_posts', MASTODON_FEED_TEXT_NO_POSTS );
	update_option( 'mastodon_feed_text_boosted', MASTODON_FEED_TEXT_BOOSTED );
	update_option( 'mastodon_feed_text_show_content', MASTODON_FEED_TEXT_SHOW_CONTENT );
	update_option( 'mastodon_feed_text_predatetime', MASTODON_FEED_TEXT_PREDATETIME );
	update_option( 'mastodon_feed_text_postdatetime', MASTODON_FEED_TEXT_POSTDATETIME );
	update_option( 'mastodon_feed_text_edited', MASTODON_FEED_TEXT_EDITED );
	update_option( 'mastodon_feed_datetime_format', MASTODON_FEED_DATETIME_FORMAT );

	add_settings_error(
		'mastodon_feed_messages',
		'mastodon_feed_settings_reset',
		__( 'All Mastodon Feed settings have been reset to their default values.', 'mastodon-feed' ),
		'success'
	);
}

/**
 * Clear all cached Mastodon feed data.
 *
 * Verifies nonce and permissions before deleting all transients related to
 * Mastodon feed caching from the WordPress options table.
 */
function mastodon_feed_clear_cache() {
	if ( ! isset( $_POST['mastodon_feed_clear_cache_nonce'] ) ||
			! wp_verify_nonce( $_POST['mastodon_feed_clear_cache_nonce'], 'mastodon_feed_clear_cache' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_mastodon_feed_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_mastodon_feed_' ) . '%'
		)
	);

	add_settings_error(
		'mastodon_feed_messages',
		'mastodon_feed_cache_cleared',
		__( 'Mastodon feed cache has been cleared successfully.', 'mastodon-feed' ),
		'success'
	);
}

/**
 * Look up a Mastodon account by handle and return account details.
 *
 * Core lookup logic shared by both admin form handler and REST API endpoint.
 * Validates handle format, queries the Mastodon instance API, and returns
 * account information including ID, display name, and URLs.
 *
 * @param string $handle Mastodon handle in format username@instance.domain or @username@instance.domain.
 *
 * @return array {
 *     Account lookup result with success status and data or error information.
 *
 *     @type bool   $success      Whether the lookup was successful.
 *     @type string $error_code   Error code if unsuccessful (e.g., 'invalid_handle', 'account_not_found').
 *     @type string $message      Human-readable success or error message.
 *     @type int    $status       HTTP status code.
 *     @type string $instance     Mastodon instance domain (on success).
 *     @type string $account_id   Account ID on the instance (on success).
 *     @type string $display_name Account display name (on success).
 *     @type string $acct         Account handle (on success).
 *     @type array  $account      Full account data array (on success).
 * }
 */
function mastodon_feed_do_account_lookup( $handle ) {
	// Remove leading @ if present.
	$clean_handle = ltrim( trim( $handle ), '@' );
	$handle_parts = explode( '@', $clean_handle );

	// Validate handle format.
	if ( count( $handle_parts ) !== 2 || empty( $handle_parts[0] ) || empty( $handle_parts[1] ) ) {
		return array(
			'success'    => false,
			'error_code' => 'invalid_handle',
			'message'    => __( 'Invalid handle format. Please use: username@instance.domain', 'mastodon-feed' ),
			'status'     => 400,
		);
	}

	list($username, $instance_domain) = $handle_parts;

	// Validate instance domain has at least one dot.
	if ( strpos( $instance_domain, '.' ) === false ) {
		return array(
			'success'    => false,
			'error_code' => 'invalid_domain',
			'message'    => __( 'Invalid instance domain. Please include the full domain (e.g., mastodon.social)', 'mastodon-feed' ),
			'status'     => 400,
		);
	}

	// Sanitize instance domain (remove any protocols, paths, or special chars).
	$instance_domain = preg_replace( '/^https?:\/\//', '', $instance_domain );
	$instance_domain = preg_replace( '/[^a-zA-Z0-9.-].*$/', '', $instance_domain );

	// Build the API URL.
	$search_url = sprintf(
		'https://%s/api/v1/accounts/lookup?acct=%s',
		$instance_domain,
		urlencode( $clean_handle )
	);

	// Make the request.
	$timeout  = get_option( 'mastodon_feed_http_timeout', MASTODON_FEED_HTTP_TIMEOUT );
	$response = wp_remote_get( $search_url, array( 'timeout' => $timeout ) );

	// Check for WordPress HTTP errors.
	if ( is_wp_error( $response ) ) {
		$error_message = $response->get_error_message();

		// Parse common network errors to provide user-friendly messages.
		if ( strpos( $error_message, 'Could not resolve host' ) !== false ||
			strpos( $error_message, 'cURL error 6' ) !== false ||
			strpos( $error_message, 'Name or service not known' ) !== false ) {
			return array(
				'success'    => false,
				'error_code' => 'domain_not_found',
				'message'    => __( 'Could not connect to instance domain. Please verify the domain exists and is spelled correctly.', 'mastodon-feed' ),
				'status'     => 500,
			);
		}

		if ( strpos( $error_message, 'Connection timed out' ) !== false ||
			strpos( $error_message, 'cURL error 28' ) !== false ||
			strpos( $error_message, 'Operation timed out' ) !== false ) {
			return array(
				'success'    => false,
				'error_code' => 'connection_timeout',
				'message'    => __( 'Connection timed out. The instance may be temporarily unreachable or slow to respond.', 'mastodon-feed' ),
				'status'     => 500,
			);
		}

		if ( strpos( $error_message, 'SSL' ) !== false ||
			strpos( $error_message, 'certificate' ) !== false ||
			strpos( $error_message, 'cURL error 60' ) !== false ) {
			return array(
				'success'    => false,
				'error_code' => 'ssl_error',
				'message'    => __( 'SSL certificate error. The instance may have an invalid or expired security certificate.', 'mastodon-feed' ),
				'status'     => 500,
			);
		}

		// Generic error for other network issues.
		return array(
			'success'    => false,
			'error_code' => 'api_request_failed',
			/* translators: %s: Error message from the API request */
			'message'    => sprintf( __( 'Failed to lookup account: %s', 'mastodon-feed' ), $error_message ),
			'status'     => 500,
		);
	}

	// Check HTTP status code.
	$status_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $status_code ) {
		if ( 404 === $status_code ) {
			return array(
				'success'    => false,
				'error_code' => 'account_not_found',
				'message'    => __( 'Account not found. Please check the username and try again.', 'mastodon-feed' ),
				'status'     => 404,
			);
		}

		if ( 401 === $status_code || 403 === $status_code ) {
			return array(
				'success'    => false,
				'error_code' => 'access_denied',
				'message'    => __( 'Access denied by the instance. The account may be private or the instance may have restrictions.', 'mastodon-feed' ),
				'status'     => $status_code,
			);
		}

		if ( 500 === $status_code || 502 === $status_code || 503 === $status_code || 504 === $status_code ) {
			return array(
				'success'    => false,
				'error_code' => 'server_error',
				'message'    => __( 'Instance server error. The Mastodon instance may be experiencing technical difficulties. Please try again later.', 'mastodon-feed' ),
				'status'     => $status_code,
			);
		}

		if ( 429 === $status_code ) {
			return array(
				'success'    => false,
				'error_code' => 'rate_limited',
				'message'    => __( 'Rate limit exceeded. Please wait a few minutes before trying again.', 'mastodon-feed' ),
				'status'     => 429,
			);
		}

		// Generic error for other HTTP status codes.
		return array(
			'success'    => false,
			'error_code' => 'api_error',
			/* translators: %d: HTTP status code (e.g. 404, 500) */
			'message'    => sprintf( __( 'Mastodon API returned HTTP status %d. Please verify the instance domain is correct.', 'mastodon-feed' ), $status_code ),
			'status'     => $status_code,
		);
	}

	// Parse JSON response.
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return array(
			'success'    => false,
			'error_code' => 'json_parse_error',
			'message'    => __( 'Failed to parse API response.', 'mastodon-feed' ),
			'status'     => 500,
		);
	}

	// Check if account was found.
	if ( empty( $data ) || ! is_array( $data ) || empty( $data['id'] ) ) {
		return array(
			'success'    => false,
			'error_code' => 'account_not_found',
			'message'    => __( 'No account found with that handle. Make sure the handle is correct and the instance is accessible.', 'mastodon-feed' ),
			'status'     => 404,
		);
	}

	// Return success with account data.
	return array(
		'success'      => true,
		'instance'     => $instance_domain,
		'account_id'   => $data['id'],
		'display_name' => $data['display_name'] ?? $username,
		'acct'         => $data['acct'] ?? $clean_handle,
		'account'      => array(
			'id'       => $data['id'],
			'acct'     => $data['acct'] ?? $clean_handle,
			'username' => $data['username'] ?? $username,
			'url'      => $data['url'] ?? '',
		),
	);
}

/**
 * Handle account lookup form submission from admin settings page.
 *
 * Verifies nonce and permissions before delegating to mastodon_feed_do_account_lookup().
 * This is the form handler specifically for the admin settings page interface.
 *
 * @return array Account lookup result array with success status and data or error information.
 */
function mastodon_feed_handle_lookup() {
	// Verify nonce.
	if ( ! isset( $_POST['mastodon_feed_lookup_nonce'] ) ||
		! wp_verify_nonce( $_POST['mastodon_feed_lookup_nonce'], 'mastodon_feed_lookup_account' ) ) {
		return array(
			'success' => false,
			'message' => __( 'Security verification failed. Please try again.', 'mastodon-feed' ),
		);
	}

	// Check permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		return array(
			'success' => false,
			'message' => __( 'You do not have permission to perform this action.', 'mastodon-feed' ),
		);
	}

	// Get and sanitize the handle.
	$handle = isset( $_POST['mastodon_feed_lookup_handle'] ) ? sanitize_text_field( $_POST['mastodon_feed_lookup_handle'] ) : '';

	if ( empty( $handle ) ) {
		return array(
			'success' => false,
			'message' => __( 'Please enter a Mastodon handle.', 'mastodon-feed' ),
		);
	}

	// Call the shared lookup function.
	return mastodon_feed_do_account_lookup( $handle );
}

/**
 * Render text input field for settings.
 *
 * @param array $args Field arguments including option_name, default, placeholder, description, and suffix.
 */
function render_text_field( $args ) {
	$option_name = $args['option_name'];
	$default     = $args['default'] ?? '';
	$value       = get_option( $option_name, $default );
	$placeholder = $args['placeholder'] ?? '';
	$description = $args['description'] ?? '';
	$suffix      = $args['suffix'] ?? '';
	?>
	<input type="text"
			id="<?php echo esc_attr( $option_name ); ?>"
			name="<?php echo esc_attr( $option_name ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
			<?php
			if ( $placeholder ) :
				?>
				placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php endif; ?> />
	<?php if ( $suffix ) : ?>
		<?php echo esc_html( $suffix ); ?>
<?php endif; ?>
	<?php if ( $description ) : ?>
	<p class="description"><?php echo wp_kses_post( $description ); ?></p>
<?php endif; ?>
	<?php
}

/**
 * Render number input field for settings.
 *
 * @param array $args Field arguments including option_name, default, description, and suffix.
 */
function render_number_field( $args ) {
	$option_name = $args['option_name'];
	$default     = $args['default'] ?? 0;
	$value       = get_option( $option_name, $default );
	$description = $args['description'] ?? '';
	$suffix      = $args['suffix'] ?? '';
	?>
	<input type="number"
			id="<?php echo esc_attr( $option_name ); ?>"
			name="<?php echo esc_attr( $option_name ); ?>"
			value="<?php echo esc_attr( $value ); ?>"/>
	<?php if ( $suffix ) : ?>
		<?php echo esc_html( $suffix ); ?>
<?php endif; ?>
	<?php if ( $description ) : ?>
	<p class="description"><?php echo wp_kses_post( $description ); ?></p>
<?php endif; ?>
	<?php
}

/**
 * Render checkbox input field for settings.
 *
 * @param array $args Field arguments including option_name, default, and description.
 */
function render_checkbox_field( $args ) {
	$option_name = $args['option_name'];
	$default     = $args['default'] ?? false;
	$value       = get_option( $option_name, $default ? '1' : '0' );
	$description = $args['description'] ?? '';
	?>
	<input type="checkbox"
			id="<?php echo esc_attr( $option_name ); ?>"
			name="<?php echo esc_attr( $option_name ); ?>"
			value="1"
			<?php checked( 1, $value ); ?> />
	<?php if ( $description ) : ?>
	<p class="description"><?php echo wp_kses_post( $description ); ?></p>
<?php endif; ?>
	<?php
}

/**
 * Render color picker field for settings.
 *
 * @param array $args Field arguments including option_name, default, and description.
 */
function render_color_field( $args ) {
	$option_name = $args['option_name'];
	$default     = $args['default'] ?? '';
	$value       = get_option( $option_name, $default );
	$description = $args['description'] ?? '';
	?>
	<input type="text"
			class="mastodon-feed-color-picker"
			id="<?php echo esc_attr( $option_name ); ?>"
			name="<?php echo esc_attr( $option_name ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			data-default-color="<?php echo esc_attr( $default ); ?>"
			data-alpha-enabled="true"/>
	<?php if ( $description ) : ?>
	<p class="description"><?php echo wp_kses_post( $description ); ?></p>
<?php endif; ?>
	<?php
}

/**
 * Render select dropdown field for settings.
 *
 * @param array $args Field arguments including option_name, default, options, and description.
 */
function render_select_field( $args ) {
	$option_name = $args['option_name'];
	$default     = $args['default'] ?? '';
	$value       = get_option( $option_name, $default );
	$options     = $args['options'] ?? array();
	$description = $args['description'] ?? '';
	?>
	<select id="<?php echo esc_attr( $option_name ); ?>" name="<?php echo esc_attr( $option_name ); ?>">
		<?php foreach ( $options as $option_value => $option_label ) : ?>
			<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>>
				<?php echo esc_html( $option_label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php if ( $description ) : ?>
	<p class="description"><?php echo wp_kses_post( $description ); ?></p>
<?php endif; ?>
	<?php
}

/**
 * Register all plugin settings, sections, and fields with WordPress.
 *
 * Registers settings for general configuration, feed filters, display options,
 * styling, and text customization with the WordPress Settings API.
 */
function mastodon_feed_register_settings() {
	// General Settings Group.
	register_setting( 'mastodon_feed_general', 'mastodon_feed_default_instance', 'sanitize_text_field' );
	register_setting( 'mastodon_feed_general', 'mastodon_feed_limit', 'intval' );
	register_setting( 'mastodon_feed_general', 'mastodon_feed_cache_interval', 'intval' );
	register_setting( 'mastodon_feed_general', 'mastodon_feed_http_timeout', 'intval' );

	add_settings_section(
		'mastodon_feed_general_section',
		__( 'General Settings', 'mastodon-feed' ),
		'__return_false',
		'mastodon_feed_general_settings'
	);

	add_settings_field(
		'mastodon_feed_default_instance',
		__( 'Default Mastodon instance hostname', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_text_field',
		'mastodon_feed_general_settings',
		'mastodon_feed_general_section',
		array(
			'label_for'   => 'mastodon_feed_default_instance',
			'option_name' => 'mastodon_feed_default_instance',
			'default'     => MASTODON_FEED_DEFAULT_INSTANCE,
			'placeholder' => 'mastodon.social',
			'description' => __( 'The Mastodon instance domain without https://. This is only used when not specified by the Block or shortcode.', 'mastodon-feed' ),
		)
	);

	add_settings_field(
		'mastodon_feed_limit',
		__( 'Default number of Posts to display', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_number_field',
		'mastodon_feed_general_settings',
		'mastodon_feed_general_section',
		array(
			'label_for'   => 'mastodon_feed_limit',
			'option_name' => 'mastodon_feed_limit',
			'default'     => MASTODON_FEED_LIMIT,
		)
	);

	add_settings_field(
		'mastodon_feed_cache_interval',
		__( 'Mastodon feed cache timeout', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_number_field',
		'mastodon_feed_general_settings',
		'mastodon_feed_general_section',
		array(
			'label_for'   => 'mastodon_feed_cache_interval',
			'option_name' => 'mastodon_feed_cache_interval',
			'default'     => HOUR_IN_SECONDS,
			'description' => __( 'How long to cache feed data before fetching from Mastodon again. Default is 3600 seconds (1 hour). Higher values reduce API calls but show older content.', 'mastodon-feed' ),
			'suffix'      => __( 'seconds', 'mastodon-feed' ),
		)
	);

	add_settings_field(
		'mastodon_feed_http_timeout',
		__( 'Mastodon API request timeout', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_number_field',
		'mastodon_feed_general_settings',
		'mastodon_feed_general_section',
		array(
			'label_for'   => 'mastodon_feed_http_timeout',
			'option_name' => 'mastodon_feed_http_timeout',
			'default'     => MASTODON_FEED_HTTP_TIMEOUT,
			'description' => __( 'How long to wait for Mastodon API responses before timing out. Default is 5 seconds. Increase if experiencing timeout errors.', 'mastodon-feed' ),
			'suffix'      => __( 'seconds', 'mastodon-feed' ),
		)
	);

	// Feed Filters Group.
	register_setting( 'mastodon_feed_filters', 'mastodon_feed_exclude_boosts', __NAMESPACE__ . '\validate_boolean_option' );
	register_setting( 'mastodon_feed_filters', 'mastodon_feed_exclude_replies', __NAMESPACE__ . '\validate_boolean_option' );
	register_setting( 'mastodon_feed_filters', 'mastodon_feed_only_pinned', __NAMESPACE__ . '\validate_boolean_option' );
	register_setting( 'mastodon_feed_filters', 'mastodon_feed_only_media', __NAMESPACE__ . '\validate_boolean_option' );
	register_setting( 'mastodon_feed_filters', 'mastodon_feed_tagged', __NAMESPACE__ . '\sanitize_tag_field' );

	add_settings_section(
		'mastodon_feed_filters_section',
		__( 'Feed Filters', 'mastodon-feed' ),
		'__return_false',
		'mastodon_feed_filters_settings'
	);

	add_settings_field(
		'mastodon_feed_only_pinned',
		__( 'Only show Posts I have Pinned', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_checkbox_field',
		'mastodon_feed_filters_settings',
		'mastodon_feed_filters_section',
		array(
			'label_for'   => 'mastodon_feed_only_pinned',
			'option_name' => 'mastodon_feed_only_pinned',
			'default'     => MASTODON_FEED_ONLY_PINNED,
		)
	);

	add_settings_field(
		'mastodon_feed_only_media',
		__( 'Only show Posts containing media', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_checkbox_field',
		'mastodon_feed_filters_settings',
		'mastodon_feed_filters_section',
		array(
			'label_for'   => 'mastodon_feed_only_media',
			'option_name' => 'mastodon_feed_only_media',
			'default'     => MASTODON_FEED_ONLY_MEDIA,
		)
	);

	add_settings_field(
		'mastodon_feed_tagged',
		__( 'Only show Posts tagged with', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_text_field',
		'mastodon_feed_filters_settings',
		'mastodon_feed_filters_section',
		array(
			'label_for'   => 'mastodon_feed_tagged',
			'option_name' => 'mastodon_feed_tagged',
			'default'     => MASTODON_FEED_TAGGED,
			'description' => __( 'Filter posts to only show those with a specific tag. Enter a tag name without the # symbol.', 'mastodon-feed' ),
			'suffix'      => __( '(e.g., fediverse - without #)', 'mastodon-feed' ),
		)
	);

	add_settings_field(
		'mastodon_feed_exclude_boosts',
		__( 'Do not display Posts I have Boosted', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_checkbox_field',
		'mastodon_feed_filters_settings',
		'mastodon_feed_filters_section',
		array(
			'label_for'   => 'mastodon_feed_exclude_boosts',
			'option_name' => 'mastodon_feed_exclude_boosts',
			'default'     => MASTODON_FEED_EXCLUDE_BOOSTS,
		)
	);

	add_settings_field(
		'mastodon_feed_exclude_replies',
		__( 'Do not display Posts I have replied to', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_checkbox_field',
		'mastodon_feed_filters_settings',
		'mastodon_feed_filters_section',
		array(
			'label_for'   => 'mastodon_feed_exclude_replies',
			'option_name' => 'mastodon_feed_exclude_replies',
			'default'     => MASTODON_FEED_EXCLUDE_REPLIES,
		)
	);

	// Display Options Group.
	register_setting( 'mastodon_feed_display', 'mastodon_feed_link_target', __NAMESPACE__ . '\validate_link_target' );
	register_setting( 'mastodon_feed_display', 'mastodon_feed_show_preview_cards', __NAMESPACE__ . '\validate_boolean_option' );
	register_setting( 'mastodon_feed_display', 'mastodon_feed_show_post_author', __NAMESPACE__ . '\validate_boolean_option' );
	register_setting( 'mastodon_feed_display', 'mastodon_feed_show_datetime', __NAMESPACE__ . '\validate_boolean_option' );
	register_setting( 'mastodon_feed_display', 'mastodon_feed_datetime_format', 'sanitize_text_field' );

	add_settings_section(
		'mastodon_feed_display_section',
		__( 'Display Options', 'mastodon-feed' ),
		'__return_false',
		'mastodon_feed_display_settings'
	);

	add_settings_field(
		'mastodon_feed_link_target',
		__( 'Links within Posts should open in', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_select_field',
		'mastodon_feed_display_settings',
		'mastodon_feed_display_section',
		array(
			'label_for'   => 'mastodon_feed_link_target',
			'option_name' => 'mastodon_feed_link_target',
			'default'     => MASTODON_FEED_LINKTARGET,
			'options'     => array(
				'_blank'  => __( 'a new window or tab (_blank)', 'mastodon-feed' ),
				'_self'   => __( 'the same frame as it was clicked (_self)', 'mastodon-feed' ),
				'_parent' => __( 'the parent frame (_parent)', 'mastodon-feed' ),
				'_top'    => __( 'the full body of the window (_top)', 'mastodon-feed' ),
			),
		)
	);

	add_settings_field(
		'mastodon_feed_show_preview_cards',
		__( 'Show Preview Cards from Posts', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_checkbox_field',
		'mastodon_feed_display_settings',
		'mastodon_feed_display_section',
		array(
			'label_for'   => 'mastodon_feed_show_preview_cards',
			'option_name' => 'mastodon_feed_show_preview_cards',
			'default'     => MASTODON_FEED_SHOW_PREVIEWCARDS,
		)
	);

	add_settings_field(
		'mastodon_feed_show_post_author',
		__( 'Show the Author of a Post', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_checkbox_field',
		'mastodon_feed_display_settings',
		'mastodon_feed_display_section',
		array(
			'label_for'   => 'mastodon_feed_show_post_author',
			'option_name' => 'mastodon_feed_show_post_author',
			'default'     => MASTODON_FEED_SHOW_POST_AUTHOR,
		)
	);

	add_settings_field(
		'mastodon_feed_show_datetime',
		__( 'Show the date & time of a Post', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_checkbox_field',
		'mastodon_feed_display_settings',
		'mastodon_feed_display_section',
		array(
			'label_for'   => 'mastodon_feed_show_datetime',
			'option_name' => 'mastodon_feed_show_datetime',
			'default'     => MASTODON_FEED_SHOW_DATETIME,
		)
	);

	add_settings_field(
		'mastodon_feed_datetime_format',
		__( 'PHP date & time format', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_text_field',
		'mastodon_feed_display_settings',
		'mastodon_feed_display_section',
		array(
			'label_for'   => 'mastodon_feed_datetime_format',
			'option_name' => 'mastodon_feed_datetime_format',
			'default'     => MASTODON_FEED_DATETIME_FORMAT,
			/* translators: %s: Example date format (e.g., Y-m-d h:i a) */
			'suffix'      => sprintf( __( '(e.g., %s)', 'mastodon-feed' ), MASTODON_FEED_DATETIME_FORMAT ),
			'description' => __( 'Please see <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">PHP DateTimeInterface::format</a> for available formatting options.', 'mastodon-feed' ),
		)
	);

	// Styling Group.
	register_setting( 'mastodon_feed_styling', 'mastodon_feed_style_bg_color', __NAMESPACE__ . '\validate_color_option' );
	register_setting( 'mastodon_feed_styling', 'mastodon_feed_style_font_color', __NAMESPACE__ . '\validate_color_option' );
	register_setting( 'mastodon_feed_styling', 'mastodon_feed_accent_color', __NAMESPACE__ . '\validate_color_option' );
	register_setting( 'mastodon_feed_styling', 'mastodon_feed_accent_font_color', __NAMESPACE__ . '\validate_color_option' );
	register_setting( 'mastodon_feed_styling', 'mastodon_feed_style_border_radius', __NAMESPACE__ . '\validate_border_radius' );

	add_settings_section(
		'mastodon_feed_styling_section',
		__( 'Styling', 'mastodon-feed' ),
		'__return_false',
		'mastodon_feed_styling_settings'
	);

	add_settings_field(
		'mastodon_feed_style_bg_color',
		__( 'Post background color', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_color_field',
		'mastodon_feed_styling_settings',
		'mastodon_feed_styling_section',
		array(
			'label_for'   => 'mastodon_feed_style_bg_color',
			'option_name' => 'mastodon_feed_style_bg_color',
			'default'     => MASTODON_FEED_STYLE_BG_COLOR,
			'description' => __( 'The background color of each post.', 'mastodon-feed' ),
		)
	);

	add_settings_field(
		'mastodon_feed_style_font_color',
		__( 'Post font color', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_color_field',
		'mastodon_feed_styling_settings',
		'mastodon_feed_styling_section',
		array(
			'label_for'   => 'mastodon_feed_style_font_color',
			'option_name' => 'mastodon_feed_style_font_color',
			'default'     => MASTODON_FEED_STYLE_FONT_COLOR,
			'description' => __( 'The text color of each post.', 'mastodon-feed' ),
		)
	);

	add_settings_field(
		'mastodon_feed_accent_color',
		__( 'Accent color', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_color_field',
		'mastodon_feed_styling_settings',
		'mastodon_feed_styling_section',
		array(
			'label_for'   => 'mastodon_feed_accent_color',
			'option_name' => 'mastodon_feed_accent_color',
			'default'     => MASTODON_FEED_STYLE_ACCENT_COLOR,
			'description' => __( 'An accent color used for links, the Preview Card hover background, and Boost author background.', 'mastodon-feed' ),
		)
	);

	add_settings_field(
		'mastodon_feed_accent_font_color',
		__( 'Accent font color', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_color_field',
		'mastodon_feed_styling_settings',
		'mastodon_feed_styling_section',
		array(
			'label_for'   => 'mastodon_feed_accent_font_color',
			'option_name' => 'mastodon_feed_accent_font_color',
			'default'     => MASTODON_FEED_STYLE_ACCENT_FONT_COLOR,
			'description' => __( 'The font color used when the accent color is active.', 'mastodon-feed' ),
		)
	);

	add_settings_field(
		'mastodon_feed_style_border_radius',
		__( 'Post background border radius', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_text_field',
		'mastodon_feed_styling_settings',
		'mastodon_feed_styling_section',
		array(
			'label_for'   => 'mastodon_feed_style_border_radius',
			'option_name' => 'mastodon_feed_style_border_radius',
			'default'     => MASTODON_FEED_STYLE_BORDER_RADIUS,
			'description' => __( 'The rounded border radius used for containers (e.g., 0.25rem, 25px, 0).', 'mastodon-feed' ),
		)
	);

	// Text Customization Group.
	register_setting( 'mastodon_feed_text', 'mastodon_feed_text_no_posts', 'sanitize_text_field' );
	register_setting( 'mastodon_feed_text', 'mastodon_feed_text_boosted', 'sanitize_text_field' );
	register_setting( 'mastodon_feed_text', 'mastodon_feed_text_show_content', 'sanitize_text_field' );
	register_setting( 'mastodon_feed_text', 'mastodon_feed_text_predatetime', 'sanitize_text_field' );
	register_setting( 'mastodon_feed_text', 'mastodon_feed_text_postdatetime', 'sanitize_text_field' );
	register_setting( 'mastodon_feed_text', 'mastodon_feed_text_edited', 'sanitize_text_field' );

	add_settings_section(
		'mastodon_feed_text_section',
		__( 'Text Customization', 'mastodon-feed' ),
		'__return_false',
		'mastodon_feed_text_settings'
	);

	add_settings_field(
		'mastodon_feed_text_no_posts',
		__( 'Text to display when there are no Posts', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_text_field',
		'mastodon_feed_text_settings',
		'mastodon_feed_text_section',
		array(
			'label_for'   => 'mastodon_feed_text_no_posts',
			'option_name' => 'mastodon_feed_text_no_posts',
			'default'     => MASTODON_FEED_TEXT_NO_POSTS,
		)
	);

	add_settings_field(
		'mastodon_feed_text_boosted',
		__( 'Text to display for Boosted Posts', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_text_field',
		'mastodon_feed_text_settings',
		'mastodon_feed_text_section',
		array(
			'label_for'   => 'mastodon_feed_text_boosted',
			'option_name' => 'mastodon_feed_text_boosted',
			'default'     => MASTODON_FEED_TEXT_BOOSTED,
		)
	);

	add_settings_field(
		'mastodon_feed_text_edited',
		__( 'Text to display for Edited Posts', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_text_field',
		'mastodon_feed_text_settings',
		'mastodon_feed_text_section',
		array(
			'label_for'   => 'mastodon_feed_text_edited',
			'option_name' => 'mastodon_feed_text_edited',
			'default'     => MASTODON_FEED_TEXT_EDITED,
		)
	);

	add_settings_field(
		'mastodon_feed_text_show_content',
		__( 'Text to display for Show Content', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_text_field',
		'mastodon_feed_text_settings',
		'mastodon_feed_text_section',
		array(
			'label_for'   => 'mastodon_feed_text_show_content',
			'option_name' => 'mastodon_feed_text_show_content',
			'default'     => MASTODON_FEED_TEXT_SHOW_CONTENT,
		)
	);

	add_settings_field(
		'mastodon_feed_text_predatetime',
		__( 'Text to display before Post date & time', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_text_field',
		'mastodon_feed_text_settings',
		'mastodon_feed_text_section',
		array(
			'label_for'   => 'mastodon_feed_text_predatetime',
			'option_name' => 'mastodon_feed_text_predatetime',
			'default'     => MASTODON_FEED_TEXT_PREDATETIME,
		)
	);

	add_settings_field(
		'mastodon_feed_text_postdatetime',
		__( 'Text to display after Post date & time', 'mastodon-feed' ),
		__NAMESPACE__ . '\render_text_field',
		'mastodon_feed_text_settings',
		'mastodon_feed_text_section',
		array(
			'label_for'   => 'mastodon_feed_text_postdatetime',
			'option_name' => 'mastodon_feed_text_postdatetime',
			'default'     => MASTODON_FEED_TEXT_POSTDATETIME,
		)
	);
}

/**
 * Validate boolean option values.
 *
 * @param string $value The value to validate ('1' or '0').
 *
 * @return string Returns '1' or '0', defaults to '0' for invalid values.
 */
function validate_boolean_option( $value ) {
	return ( '1' === $value || '0' === $value ) ? $value : '0';
}

/**
 * Sanitize tag field by removing leading # symbol.
 *
 * @param string $value The tag value to sanitize.
 *
 * @return string Sanitized tag without leading #.
 */
function sanitize_tag_field( $value ) {
	$value = sanitize_text_field( $value );
	// Strip leading # symbol if present.
	$value = ltrim( $value, '#' );
	return $value;
}

/**
 * Validate color option values.
 *
 * Accepts hex colors, rgb/rgba, hsl/hsla, and named colors like 'transparent'.
 *
 * @param string $value The color value to validate.
 *
 * @return string Valid color value or empty string for invalid colors.
 */
function validate_color_option( $value ) {
	$value = sanitize_text_field( $value );
	// Allow hex colors (#fff, #ffffff, #ffffffff), rgb/rgba, hsl/hsla, and named colors.
	if ( preg_match( '/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6}|[a-fA-F0-9]{8})$/', $value ) ) {
		return $value;
	}
	if ( preg_match( '/^rgba?\s*\([^)]+\)$/i', $value ) ) {
		return $value;
	}
	if ( preg_match( '/^hsla?\s*\([^)]+\)$/i', $value ) ) {
		return $value;
	}
	if ( preg_match( '/^(transparent|currentColor)$/i', $value ) ) {
		return strtolower( $value );
	}
	return ''; // Invalid color.
}

/**
 * Validate link target option values.
 *
 * @param string $value The link target value to validate.
 *
 * @return string Valid target (_blank, _self, _parent, _top) or '_blank' as default.
 */
function validate_link_target( $value ) {
	$allowed_targets = array( '_blank', '_self', '_parent', '_top' );
	return in_array( $value, $allowed_targets, true ) ? $value : '_blank';
}

/**
 * Validate border radius option values.
 *
 * Accepts valid CSS length units: px, em, rem, %, vh, vw, etc.
 *
 * @param string $value The border radius value to validate.
 *
 * @return string Valid CSS length value or '0.25rem' as default.
 */
function validate_border_radius( $value ) {
	$value = sanitize_text_field( $value );
	// Allow valid CSS length units: px, em, rem, %, vh, vw, etc.
	if ( preg_match( '/^(0|[0-9]+(\.[0-9]+)?(px|em|rem|%|vh|vw|vmin|vmax|ch|ex))$/i', $value ) ) {
		return $value;
	}
	return '0.25rem'; // Default fallback.
}

add_action(
	'admin_menu',
	function () {
		add_options_page(
			__( 'Mastodon Feed Settings', 'mastodon-feed' ),
			__( 'Mastodon Feed', 'mastodon-feed' ),
			'manage_options',
			'mastodon-feed-settings',
			__NAMESPACE__ . '\mastodon_feed_settings_page'
		);
	}
);

add_action( 'admin_init', __NAMESPACE__ . '\mastodon_feed_register_settings' );

/**
 * Display Mastodon feed via shortcode.
 *
 * Main shortcode handler that fetches and renders Mastodon posts with customizable
 * filtering, styling, and display options. Supports both account-based and tag-based feeds.
 *
 * @param array $atts Shortcode attributes including instance, account, tag, limit, filters, and display options.
 *
 * @return string HTML output of the Mastodon feed or error message.
 */
function display_feed( $atts ) {
	$atts = shortcode_atts(
		array(
			'instance'          => get_option( 'mastodon_feed_default_instance', MASTODON_FEED_DEFAULT_INSTANCE ),
			'account'           => false,
			'tag'               => false,
			'limit'             => get_option( 'mastodon_feed_limit', MASTODON_FEED_LIMIT ),
			'excludeboosts'     => get_option( 'mastodon_feed_exclude_boosts', MASTODON_FEED_EXCLUDE_BOOSTS ),
			'excludereplies'    => get_option( 'mastodon_feed_exclude_replies', MASTODON_FEED_EXCLUDE_REPLIES ),
			'onlypinned'        => get_option( 'mastodon_feed_only_pinned', MASTODON_FEED_ONLY_PINNED ),
			'onlymedia'         => get_option( 'mastodon_feed_only_media', MASTODON_FEED_ONLY_MEDIA ),
			'tagged'            => get_option( 'mastodon_feed_tagged', MASTODON_FEED_TAGGED ),
			'linktarget'        => get_option( 'mastodon_feed_link_target', MASTODON_FEED_LINKTARGET ),
			'showpreviewcards'  => get_option( 'mastodon_feed_show_preview_cards', MASTODON_FEED_SHOW_PREVIEWCARDS ),
			'showpostauthor'    => get_option( 'mastodon_feed_show_post_author', MASTODON_FEED_SHOW_POST_AUTHOR ),
			'showdatetime'      => get_option( 'mastodon_feed_show_datetime', MASTODON_FEED_SHOW_DATETIME ),
			'text-noposts'      => get_option( 'mastodon_feed_text_no_posts', MASTODON_FEED_TEXT_NO_POSTS ),
			'text-boosted'      => get_option( 'mastodon_feed_text_boosted', MASTODON_FEED_TEXT_BOOSTED ),
			'text-showcontent'  => get_option( 'mastodon_feed_text_show_content', MASTODON_FEED_TEXT_SHOW_CONTENT ),
			'text-predatetime'  => get_option( 'mastodon_feed_text_predatetime', MASTODON_FEED_TEXT_PREDATETIME ),
			'text-postdatetime' => get_option( 'mastodon_feed_text_postdatetime', MASTODON_FEED_TEXT_POSTDATETIME ),
			'text-edited'       => get_option( 'mastodon_feed_text_edited', MASTODON_FEED_TEXT_EDITED ),
			'datetimeformat'    => get_option( 'mastodon_feed_datetime_format', MASTODON_FEED_DATETIME_FORMAT ),
		),
		array_change_key_case( $atts, CASE_LOWER )
	);

	// Sanitize tag attributes to strip # symbol.
	$tag    = ltrim( $atts['tag'], '#' );
	$tagged = ltrim( $atts['tagged'], '#' );

	// Validate that either account or tag is provided.
	if ( empty( $atts['account'] ) && empty( $tag ) ) {
		return '<div class="mastodon-feed">' . esc_html__( 'Error: Either "account" or "tag" parameter is required for the mastodon-feed shortcode.', 'mastodon-feed' ) . '</div>';
	}

	$posts = fetch_and_cache_posts(
		$atts['instance'],
		$atts['account'],
		$tag,
		$atts['limit'],
		$atts['excludeboosts'],
		$atts['excludereplies'],
		$atts['onlypinned'],
		$atts['onlymedia'],
		$tagged
	);

	if ( is_wp_error( $posts ) ) {
		return '<div class="mastodon-feed">' . esc_html( $posts->get_error_message() ) . '</div>';
	}

	if ( empty( $posts ) ) {
		return '<div class="mastodon-feed">' . esc_html( $atts['text-noposts'] ) . '</div>';
	}

	ob_start();
	?>
	<div class="mastodon-feed">
		<?php foreach ( $posts as $status ) : ?>
			<?php
			$is_reblog   = ! empty( $status['reblog'] );
			$show_status = $is_reblog ? $status['reblog'] : $status;
			?>
			<div class="mastodon-feed-post">
				<div class="account">
					<?php if ( ! empty( $atts['showpostauthor'] ) ) : ?>
						<img class="avatar" src="<?php echo esc_url( $status['account']['avatar_static'] ); ?>"
							alt="<?php echo esc_attr( $status['account']['display_name'] ); ?>">
						<a href="<?php echo esc_url( $status['account']['url'] ); ?>">
							<?php echo esc_html( $status['account']['display_name'] ); ?>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $atts['showdatetime'] ) ) : ?>
						<span class="permalink">
							<?php if ( ! empty( $atts['showpostauthor'] ) ) : ?>
								<?php echo esc_html( $atts['text-predatetime'] ); ?>
							<?php endif; ?>
							<a href="<?php echo esc_url( $status['url'] ); ?>">
								<?php echo esc_html( wp_date( $atts['datetimeformat'], strtotime( $status['created_at'] ) ) ); ?>
							</a>
							<?php echo esc_html( $atts['text-postdatetime'] ); ?>
						</span>
						<?php if ( ! empty( $status['edited_at'] ) ) : ?>
							<span class="edited"><?php echo esc_html( $atts['text-edited'] ); ?></span>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( $is_reblog ) : ?>
						<span class="booster"><?php echo esc_html( $atts['text-boosted'] ); ?></span>
					<?php endif; ?>
				</div>

				<div class="content-wrapper<?php echo $is_reblog ? ' boosted' : ''; ?>">
					<?php if ( $is_reblog ) : ?>
						<div class="account">
							<a href="<?php echo esc_url( $show_status['account']['url'] ); ?>">
								<img class="avatar"
									src="<?php echo esc_url( $show_status['account']['avatar_static'] ); ?>"
									alt="<?php echo esc_attr( $show_status['account']['display_name'] ); ?>">
								<?php echo esc_html( $show_status['account']['display_name'] ); ?>
							</a>
							<span class="permalink">
								<?php echo esc_html( $atts['text-predatetime'] ); ?>
								<a href="<?php echo esc_url( $show_status['url'] ); ?>">
									<?php echo esc_html( wp_date( $atts['datetimeformat'], strtotime( $show_status['created_at'] ) ) ); ?>
								</a>
								<?php echo esc_html( $atts['text-postdatetime'] ); ?>
							</span>
						</div>
					<?php endif; ?>

					<div class="mastodon-feed-content">
						<?php if ( ! empty( $show_status['sensitive'] ) || ! empty( $show_status['spoiler_text'] ) ) : ?>
						<div class="content-warning" role="status" aria-live="polite">
							<?php if ( ! empty( $show_status['spoiler_text'] ) ) : ?>
								<div class="title"><?php echo esc_html( $show_status['spoiler_text'] ); ?></div>
							<?php endif; ?>
							<a href="#" class="mastodon-feed-show-content" role="button"
								aria-label="
								<?php
								echo esc_attr(
									sprintf(
									/* translators: %s: The content warning text from the post */
										__( 'Show content with warning: %s', 'mastodon-feed' ),
										$show_status['spoiler_text']
									)
								);
								?>
											"><?php echo esc_html( $atts['text-showcontent'] ); ?></a>
						</div>
						<div style="display: none;" aria-hidden="true">
							<?php endif; ?>

							<?php
							$render_content = ! empty( $show_status['content'] ) ? $show_status['content'] : '';
							if ( ! empty( $show_status['emojis'] ) ) {
								foreach ( $show_status['emojis'] as $emoji ) {
									$render_content = str_replace( ':' . $emoji['shortcode'] . ':', '<img src="' . esc_url( $emoji['url'] ) . '" alt="' . esc_attr( $emoji['shortcode'] ) . '" class="emoji">', $render_content );
								}
							}
							echo wp_kses_post( $render_content );
							?>

							<?php if ( ! empty( $show_status['media_attachments'] ) && is_array( $show_status['media_attachments'] ) ) : ?>
								<div class="media">
									<?php foreach ( $show_status['media_attachments'] as $media ) : ?>
										<?php
										$alt_text = ! empty( $media['description'] ) ? $media['description'] : __( 'Media attachment', 'mastodon-feed' );
										?>
										<div class="<?php echo esc_attr( $media['type'] ); ?>">
											<a href="<?php echo esc_url( $media['url'] ); ?>"
												aria-label="
												<?php
												echo esc_attr(
													sprintf(
													/* translators: %s: Description of the media attachment */
														__( 'View media: %s', 'mastodon-feed' ),
														$alt_text
													)
												);
												?>
															">
												<img src="<?php echo esc_url( $media['preview_url'] ); ?>"
													alt="<?php echo esc_attr( $alt_text ); ?>">
											</a>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $atts['showpreviewcards'] ) && ! empty( $show_status['card'] ) ) : ?>
								<div class="card">
									<a href="<?php echo esc_url( $show_status['card']['url'] ); ?>">
										<div class="image">
											<img src="<?php echo esc_url( $show_status['card']['image'] ); ?>"
												alt="<?php echo esc_attr( $show_status['card']['title'] ); ?>">
										</div>
										<div class="meta">
											<div class="title"><?php echo esc_html( $show_status['card']['title'] ); ?></div>
											<div class="description"><?php echo esc_html( $show_status['card']['description'] ); ?></div>
										</div>
									</a>
								</div>
							<?php endif; ?>
						</div>

						<?php if ( ! empty( $show_status['sensitive'] ) || ! empty( $show_status['spoiler_text'] ) ) : ?>
					</div>
				<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

add_shortcode( 'mastodon-feed', __NAMESPACE__ . '\display_feed' );

/**
 * Register Gutenberg Block.
 */
function register_mastodon_feed_block() {
	// Only register if Gutenberg is available.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	// Register the block.
	register_block_type(
		__DIR__ . '/build/block',
		array(
			'render_callback' => __NAMESPACE__ . '\render_mastodon_feed_block',
		)
	);
}

add_action( 'init', __NAMESPACE__ . '\register_mastodon_feed_block' );

/**
 * Render callback for the block (server-side rendering).
 *
 * @param array $attributes Block attributes.
 * @return string Rendered HTML output.
 */
function render_mastodon_feed_block( $attributes ) {
	// Convert block attributes to shortcode format.
	$shortcode_atts = array(
		'instance'         => $attributes['instance'] ?? 'mastodon.social',
		'account'          => $attributes['account'] ?? '',
		'tag'              => $attributes['tag'] ?? '',
		'limit'            => $attributes['limit'] ?? 10,
		'excludeBoosts'    => $attributes['excludeBoosts'] ?? false,
		'excludeReplies'   => $attributes['excludeReplies'] ?? false,
		'onlyPinned'       => $attributes['onlyPinned'] ?? false,
		'onlyMedia'        => $attributes['onlyMedia'] ?? false,
		'showPreviewCards' => $attributes['showPreviewCards'] ?? get_option( 'mastodon_feed_show_preview_cards', MASTODON_FEED_SHOW_PREVIEWCARDS ),
		'showPostAuthor'   => $attributes['showPostAuthor'] ?? get_option( 'mastodon_feed_show_post_author', MASTODON_FEED_SHOW_POST_AUTHOR ),
		'showDateTime'     => $attributes['showDateTime'] ?? get_option( 'mastodon_feed_show_datetime', MASTODON_FEED_SHOW_DATETIME ),
		'dateTimeFormat'   => $attributes['dateTimeFormat'] ?? get_option( 'mastodon_feed_datetime_format', MASTODON_FEED_DATETIME_FORMAT ),
	);

	// Use the existing display_feed function.
	return display_feed( $shortcode_atts );
}

/**
 * Register REST API endpoint for account lookup.
 */
function register_mastodon_feed_rest_api() {
	register_rest_route(
		'mastodon-feed/v1',
		'/lookup-account',
		array(
			'methods'             => 'POST',
			'callback'            => __NAMESPACE__ . '\lookup_mastodon_account',
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
			'args'                => array(
				'handle' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => function ( $param ) {
						return ! empty( $param );
					},
				),
			),
		)
	);
}

add_action( 'rest_api_init', __NAMESPACE__ . '\register_mastodon_feed_rest_api' );

/**
 * REST API handler for account lookup (thin wrapper around shared function).
 *
 * @param WP_REST_Request $request The REST API request object.
 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error on failure.
 */
function lookup_mastodon_account( $request ) {
	$handle = $request->get_param( 'handle' );

	// Call the shared lookup function.
	$result = mastodon_feed_do_account_lookup( $handle );

	// Convert result to WP_Error or REST response.
	if ( ! $result['success'] ) {
		return new \WP_Error(
			$result['error_code'],
			$result['message'],
			array( 'status' => $result['status'] )
		);
	}

	// Return REST response with account data.
	return rest_ensure_response( $result );
}
