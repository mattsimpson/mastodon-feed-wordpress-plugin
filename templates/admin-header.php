<?php
/**
 * Admin header template for Mastodon Feed settings.
 *
 * @package Mastodon_Feed
 */

namespace IncludeMastodonFeedPlugin;

/* @var array $args Template arguments. */
$args = wp_parse_args( $args ?? array() );
?>
<div class="mastodon-feed-settings-header">
	<div class="mastodon-feed-settings-title-section">
		<h1><?php echo esc_html__( 'Mastodon Feed Settings', 'mastodon-feed' ); ?></h1>
	</div>

	<nav class="nav-tab-wrapper" aria-label="<?php esc_attr_e( 'Settings sections', 'mastodon-feed' ); ?>">
		<?php
		foreach ( $args['tabs'] as $slug => $label ) :
			$url = add_query_arg(
				array( 'tab' => $slug ),
				admin_url( 'options-general.php?page=mastodon-feed-settings' )
			);
			$active_class = $args[ $slug ] ?? '';
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="nav-tab <?php echo esc_attr( $active_class ); ?>">
				<?php echo esc_html( $label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
</div>
