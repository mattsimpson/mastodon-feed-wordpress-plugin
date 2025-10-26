/**
 * E2E tests for Mastodon Feed block in WordPress block editor
 */

const { test, expect } = require( '@wordpress/e2e-test-utils-playwright' );

test.describe( 'Mastodon Feed Block E2E', () => {
	test.beforeEach( async ( { admin, page } ) => {
		await admin.visitAdminPage( 'post-new.php' );

		// Dismiss the WordPress welcome guide if it appears
		const closeButton = page.locator(
			'.components-modal__screen-overlay button[aria-label="Close"]'
		);
		if (
			await closeButton
				.isVisible( { timeout: 2000 } )
				.catch( () => false )
		) {
			await closeButton.click();
		}
	} );

	test( 'can insert Mastodon Feed block', async ( { editor } ) => {
		// Insert block
		await editor.insertBlock( { name: 'mastodon-feed/embed' } );

		// Check block is inserted (blocks are in the editor canvas iframe)
		const block = editor.canvas.locator(
			'[data-type="mastodon-feed/embed"]'
		);
		await expect( block ).toBeVisible();
	} );

	test( 'block shows validation error without account or tag', async ( {
		editor,
	} ) => {
		// Insert block
		await editor.insertBlock( { name: 'mastodon-feed/embed' } );

		// Block should show the setup form (no error, just setup instructions)
		const setupText = editor.canvas.locator(
			'text=/Enter your Mastodon account ID/i'
		);
		await expect( setupText ).toBeVisible( { timeout: 10000 } );
	} );

	test( 'can configure block with account ID', async ( { editor } ) => {
		// Insert block
		await editor.insertBlock( { name: 'mastodon-feed/embed' } );

		// Fill in instance and account (forms are in the canvas)
		await editor.canvas.getByLabel( 'INSTANCE' ).fill( 'mastodon.social' );
		await editor.canvas.getByLabel( 'ACCOUNT ID' ).fill( '123456' );

		// Check settings are applied
		await expect( editor.canvas.getByLabel( 'INSTANCE' ) ).toHaveValue(
			'mastodon.social'
		);
		await expect( editor.canvas.getByLabel( 'ACCOUNT ID' ) ).toHaveValue(
			'123456'
		);
	} );

	test( 'can configure block with tag', async ( { editor } ) => {
		// Insert block
		await editor.insertBlock( { name: 'mastodon-feed/embed' } );

		// Fill in instance and tag (forms are in the canvas)
		await editor.canvas.getByLabel( 'INSTANCE' ).fill( 'mastodon.social' );
		await editor.canvas.getByLabel( 'TAG' ).fill( 'photography' );

		// Check settings are applied
		await expect( editor.canvas.getByLabel( 'TAG' ) ).toHaveValue(
			'photography'
		);
	} );

	test( 'can toggle filter options', async ( { editor, page } ) => {
		// Insert and configure block (Inspector Controls only appear after configuration)
		await editor.insertBlock( { name: 'mastodon-feed/embed' } );
		await editor.canvas.getByLabel( 'INSTANCE' ).fill( 'mastodon.social' );
		const accountField = editor.canvas.getByLabel( 'ACCOUNT ID' );
		await accountField.fill( '123456' );

		// Select the block to ensure inspector controls show
		await editor.canvas
			.locator( '[data-type="mastodon-feed/embed"]' )
			.click();

		// Wait for the Filter Options panel to be visible in sidebar
		// Inspector panels appear immediately after block configuration, even if API fails
		const filterPanel = page.getByRole( 'button', {
			name: /filter options/i,
		} );
		await filterPanel.waitFor( { state: 'visible', timeout: 10000 } );
		await filterPanel.click();

		// Toggle exclude boosts
		const excludeBoostsToggle = page.getByLabel( /exclude.*boost/i );
		await excludeBoostsToggle.check();
		await expect( excludeBoostsToggle ).toBeChecked();
	} );

	test( 'can toggle display options', async ( { editor, page } ) => {
		// Insert and configure block (Inspector Controls only appear after configuration)
		await editor.insertBlock( { name: 'mastodon-feed/embed' } );
		await editor.canvas.getByLabel( 'INSTANCE' ).fill( 'mastodon.social' );
		const accountField = editor.canvas.getByLabel( 'ACCOUNT ID' );
		await accountField.fill( '123456' );

		// Select the block to ensure inspector controls show
		await editor.canvas
			.locator( '[data-type="mastodon-feed/embed"]' )
			.click();

		// Wait for the Display Options panel to be visible in sidebar
		const displayPanel = page.getByRole( 'button', {
			name: /display options/i,
		} );
		await displayPanel.waitFor( { state: 'visible', timeout: 10000 } );
		await displayPanel.click();

		// Toggle show author
		const showAuthorToggle = page.getByLabel( /show.*author/i );
		await showAuthorToggle.uncheck();
		await expect( showAuthorToggle ).not.toBeChecked();
	} );

	test( 'account lookup panel is available', async ( { editor } ) => {
		// Insert block
		await editor.insertBlock( { name: 'mastodon-feed/embed' } );

		// Check account lookup button/form exists in the block
		const lookupButton = editor.canvas.getByRole( 'button', {
			name: /lookup account id/i,
		} );
		await expect( lookupButton ).toBeVisible();
	} );

	test( 'can save post with block', async ( { editor, page } ) => {
		// Insert block with valid configuration
		await editor.insertBlock( { name: 'mastodon-feed/embed' } );

		// Configure block (forms are in the canvas)
		await editor.canvas.getByLabel( 'INSTANCE' ).fill( 'mastodon.social' );
		await editor.canvas.getByLabel( 'ACCOUNT ID' ).fill( '123456' );

		// Wait for the block to finish any initial rendering
		await page.waitForTimeout( 500 );

		// Open publish panel if needed
		const publishButton = page
			.getByRole( 'region', { name: 'Editor top bar' } )
			.getByRole( 'button', { name: 'Publish', exact: true } );

		// Wait for publish button to be available
		await publishButton.waitFor( { state: 'visible', timeout: 10000 } );
		await publishButton.click();

		// Click the final publish button in the panel
		const finalPublishButton = page
			.getByRole( 'region', { name: 'Editor publish' } )
			.getByRole( 'button', { name: 'Publish', exact: true } );
		await finalPublishButton.click();

		// Check post is published (use first match to avoid strict mode violation)
		const publishedNotice = page.locator( 'text=/published/i' ).first();
		await expect( publishedNotice ).toBeVisible( { timeout: 10000 } );
	} );

	test( 'block persists settings after reload', async ( {
		editor,
		page,
	} ) => {
		// Insert and configure block
		await editor.insertBlock( { name: 'mastodon-feed/embed' } );
		await editor.canvas.getByLabel( 'INSTANCE' ).fill( 'fosstodon.org' );
		const accountField = editor.canvas.getByLabel( 'ACCOUNT ID' );
		await accountField.fill( '999999' );

		// Wait for the block to finish any initial rendering
		await page.waitForTimeout( 500 );

		// Save the post (button might say "Save draft" or "Save")
		// Try multiple button variations
		const saveButton = page
			.locator( 'button' )
			.filter( { hasText: /^(Save|Save draft)$/i } )
			.first();
		await saveButton.waitFor( { state: 'visible', timeout: 10000 } );
		await saveButton.click();

		// Wait for save to complete
		await page.waitForTimeout( 1000 ); // Give it time to save

		// Reload page
		await page.reload();

		// Wait for editor and block to load
		await editor.canvas
			.locator( '[data-type="mastodon-feed/embed"]' )
			.waitFor();

		// Select the block to show inspector controls in sidebar
		await editor.canvas
			.locator( '[data-type="mastodon-feed/embed"]' )
			.click();

		// Wait for inspector controls to appear
		const instanceInput = page
			.getByRole( 'textbox', { name: /instance/i } )
			.first();
		await instanceInput.waitFor( { state: 'visible', timeout: 5000 } );

		// Check block settings persisted (check in Feed Settings panel in inspector)
		await expect( instanceInput ).toHaveValue( 'fosstodon.org' );

		const accountInput = page
			.getByRole( 'textbox', { name: /account id/i } )
			.first();
		await expect( accountInput ).toHaveValue( '999999' );
	} );
} );
