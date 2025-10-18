/**
 * Global setup for E2E tests
 * Handles WordPress authentication before running tests
 */

const { chromium } = require( '@playwright/test' );
const path = require( 'path' );
const fs = require( 'fs' );

async function globalSetup() {
	const baseURL = process.env.WP_BASE_URL || 'http://localhost:8888';
	const username = process.env.WP_USERNAME || 'admin';
	const password = process.env.WP_PASSWORD || 'password';
	const storageStatePath = path.join(
		__dirname,
		'../../.auth/storageState.json'
	);

	console.log( 'Setting up WordPress authentication...' );

	// Launch browser
	const browser = await chromium.launch();
	const page = await browser.newPage();

	try {
		// Navigate to login page
		await page.goto( `${ baseURL }/wp-login.php` );

		// Fill in login form
		await page.fill( '#user_login', username );
		await page.fill( '#user_pass', password );
		await page.click( '#wp-submit' );

		// Wait for navigation to complete
		await page.waitForURL( /wp-admin/, { timeout: 10000 } );

		// Ensure the .auth directory exists
		const authDir = path.dirname( storageStatePath );
		if ( ! fs.existsSync( authDir ) ) {
			fs.mkdirSync( authDir, { recursive: true } );
		}

		// Save authenticated state
		await page.context().storageState( { path: storageStatePath } );

		console.log( 'WordPress authentication successful!' );
	} catch ( error ) {
		console.error( 'Authentication failed:', error );
		throw error;
	} finally {
		await browser.close();
	}
}

module.exports = globalSetup;
