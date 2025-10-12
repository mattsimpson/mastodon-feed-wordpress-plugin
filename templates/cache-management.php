<?php
/**
 * Cache Management template for Mastodon Feed.
 *
 * @package Mastodon_Feed
 */

namespace IncludeMastodonFeedPlugin;
?>

<div class="mastodon-feed-settings-page">
	<h2><?php echo esc_html__( 'Cache Management', 'mastodon-feed' ); ?></h2>
	<p><?php echo esc_html__( 'The Mastodon Feed plugin caches API responses to improve performance and reduce API calls.', 'mastodon-feed' ); ?></p>

	<form method="post" action="">
		<?php wp_nonce_field( 'mastodon_feed_clear_cache', 'mastodon_feed_clear_cache_nonce' ); ?>
		<input type="hidden" name="mastodon_feed_clear_cache" value="1" />

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php echo esc_html__( 'Clear Cache', 'mastodon-feed' ); ?></th>
					<td>
						<p class="description">
							<?php echo esc_html__( 'This will clear all cached Mastodon feed data. The next time a feed is displayed, fresh data will be fetched from the Mastodon API.', 'mastodon-feed' ); ?>
						</p>
						<button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to clear all cached Mastodon feed data?', 'mastodon-feed' ) ); ?>');">
							<?php echo esc_html__( 'Clear All Caches', 'mastodon-feed' ); ?>
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</form>

	<?php
	// Display cache clear result if available
	if ( isset( $args['cache_cleared'] ) && $args['cache_cleared'] ) {
		?>
		<div class="notice notice-success inline">
			<p><?php echo esc_html__( 'All Mastodon feed caches have been cleared successfully.', 'mastodon-feed' ); ?></p>
		</div>
		<?php
	}
	?>
</div>
