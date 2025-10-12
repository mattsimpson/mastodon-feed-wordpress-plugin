<?php
/**
 * Account Lookup template for Mastodon Feed.
 *
 * @package Mastodon_Feed
 */

namespace IncludeMastodonFeedPlugin;
?>

<div class="mastodon-feed-settings-page">
	<h2><?php echo esc_html__( 'Account Lookup Tool', 'mastodon-feed' ); ?></h2>
	<p><?php echo esc_html__( 'Use this tool to find your Mastodon account ID from your handle.', 'mastodon-feed' ); ?></p>

	<form method="post" action="">
		<?php wp_nonce_field( 'mastodon_feed_lookup_account', 'mastodon_feed_lookup_nonce' ); ?>

		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row">
						<label for="mastodon_feed_lookup_handle"><?php echo esc_html__( 'Mastodon Handle', 'mastodon-feed' ); ?></label>
					</th>
					<td>
						<input type="text"
							   id="mastodon_feed_lookup_handle"
							   name="mastodon_feed_lookup_handle"
							   placeholder="username@mastodon.social"
							   class="regular-text" />
						<p class="description">
							<?php echo esc_html__( 'Enter your full Mastodon handle (e.g., username@mastodon.social)', 'mastodon-feed' ); ?>
						</p>
					</td>
				</tr>
			</tbody>
		</table>

		<?php submit_button( __( 'Lookup Account ID', 'mastodon-feed' ), 'secondary' ); ?>
	</form>

	<?php
	// Display lookup results if available
	if ( isset( $args['lookup_result'] ) && ! empty( $args['lookup_result'] ) ) {
		$result = $args['lookup_result'];
		if ( $result['success'] ) {
			?>
			<div class="notice notice-success inline">
				<p>
					<strong><?php echo esc_html__( 'Account Found!', 'mastodon-feed' ); ?></strong><br>
					<?php echo esc_html__( 'Instance:', 'mastodon-feed' ); ?> <code><?php echo esc_html( $result['instance'] ); ?></code><br>
					<?php echo esc_html__( 'Account ID:', 'mastodon-feed' ); ?> <code><?php echo esc_html( $result['account_id'] ); ?></code><br>
					<?php echo esc_html__( 'Display Name:', 'mastodon-feed' ); ?> <?php echo esc_html( $result['display_name'] ); ?><br>
					<?php echo esc_html__( 'Username:', 'mastodon-feed' ); ?> @<?php echo esc_html( $result['acct'] ); ?>
				</p>
			</div>
			<?php
		} else {
			?>
			<div class="notice notice-error inline">
				<p><?php echo esc_html( $result['message'] ); ?></p>
			</div>
			<?php
		}
	}
	?>
</div>
