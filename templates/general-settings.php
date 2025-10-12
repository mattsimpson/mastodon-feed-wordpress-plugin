<?php
/**
 * General Settings template for Mastodon Feed.
 *
 * @package Mastodon_Feed
 */

namespace IncludeMastodonFeedPlugin;
?>

<div class="mastodon-feed-settings-page">
	<form method="post" action="options.php">
		<?php settings_fields( 'mastodon_feed_general' ); ?>
		<?php do_settings_sections( 'mastodon_feed_general_settings' ); ?>
		<?php submit_button(); ?>
	</form>

	<hr style="margin: 30px 0;">

	<h2><?php echo esc_html__( 'Reset Settings', 'mastodon-feed' ); ?></h2>
	<p><?php echo esc_html__( 'Reset all plugin settings to their default values. This action cannot be undone.', 'mastodon-feed' ); ?></p>
	<form method="post" action="">
		<?php wp_nonce_field( 'mastodon_feed_reset_settings', 'mastodon_feed_reset_settings_nonce' ); ?>
		<input type="hidden" name="mastodon_feed_reset_settings" value="1" />
		<button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to reset all Mastodon Feed settings to their default values? This action cannot be undone.', 'mastodon-feed' ) ); ?>');">
			<?php echo esc_html__( 'Reset to Defaults', 'mastodon-feed' ); ?>
		</button>
	</form>
</div>
