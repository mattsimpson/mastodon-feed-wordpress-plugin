<?php
/**
 * Display Options Settings template for Mastodon Feed.
 *
 * @package Mastodon_Feed
 */

namespace IncludeMastodonFeedPlugin;
?>

<div class="mastodon-feed-settings-page">
	<form method="post" action="options.php">
		<?php settings_fields( 'mastodon_feed_display' ); ?>
		<?php do_settings_sections( 'mastodon_feed_display_settings' ); ?>
		<?php submit_button(); ?>
	</form>
</div>
