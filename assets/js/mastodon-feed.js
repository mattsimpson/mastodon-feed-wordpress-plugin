jQuery( document ).ready( function ( $ ) {
	// Only initialize color picker if elements exist (admin settings page)
	if ( $( '.mastodon-feed-color-picker' ).length > 0 && $.fn.wpColorPicker ) {
		$( '.mastodon-feed-color-picker' ).wpColorPicker( {
			alpha: true,
			change() {
				// Optional: trigger change event for real-time preview
			},
		} );
	}

	// Handle content warning toggle without inline onclick (frontend)
	$( '.mastodon-feed' ).on( 'click', '.content-warning a', function ( e ) {
		e.preventDefault();
		const $warning = $( this ).parent( '.content-warning' );
		const $content = $warning.next( 'div' );

		$warning.hide();
		$content
			.show()
			.attr( 'aria-hidden', 'false' )
			.find( 'a, button, input, [tabindex]' )
			.first()
			.focus();
	} );
} );
