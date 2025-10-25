const { defineConfig, devices } = require( '@playwright/test' );
const path = require( 'path' );

// Set environment variables for WordPress authentication
process.env.WP_BASE_URL = process.env.WP_BASE_URL || 'http://localhost:8888';
process.env.WP_USERNAME = process.env.WP_USERNAME || 'admin';
process.env.WP_PASSWORD = process.env.WP_PASSWORD || 'password';
process.env.WP_ARTIFACTS_PATH =
	process.env.WP_ARTIFACTS_PATH || path.join( __dirname, 'artifacts' );
process.env.STORAGE_STATE_PATH =
	process.env.STORAGE_STATE_PATH ||
	path.join( process.env.WP_ARTIFACTS_PATH, 'storage-states/admin.json' );

module.exports = defineConfig( {
	testDir: './tests/e2e',
	fullyParallel: true,
	forbidOnly: !! process.env.CI,
	retries: process.env.CI ? 2 : 0,
	workers: process.env.CI ? 1 : undefined,
	reporter: 'html',
	globalSetup: require.resolve( './tests/e2e/global-setup.js' ),

	use: {
		baseURL: process.env.WP_BASE_URL,
		storageState: process.env.STORAGE_STATE_PATH,
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
	},

	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ] },
		},
	],

	// Only configure webServer if wp-env isn't already started (e.g., by CI workflow)
	webServer: process.env.WPENV_STARTED
		? undefined
		: {
				command: 'npm run wp-env start',
				url: 'http://localhost:8888/wp-admin',
				reuseExistingServer: ! process.env.CI,
				timeout: 120000,
		  },
} );
