const { defineConfig, devices } = require('@playwright/test');
const path = require('path');

// Set environment variables for WordPress authentication
process.env.WP_BASE_URL = process.env.WP_BASE_URL || 'http://localhost:8888';
process.env.WP_USERNAME = process.env.WP_USERNAME || 'admin';
process.env.WP_PASSWORD = process.env.WP_PASSWORD || 'password';

module.exports = defineConfig({
	testDir: './tests/e2e',
	fullyParallel: true,
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 2 : 0,
	workers: process.env.CI ? 1 : undefined,
	reporter: 'html',
	globalSetup: require.resolve('./tests/e2e/global-setup.js'),

	use: {
		baseURL: process.env.WP_BASE_URL,
		storageState: path.join(__dirname, '.auth/storageState.json'),
		trace: 'on-first-retry',
		screenshot: 'only-on-failure',
	},

	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
	],

	webServer: {
		command: 'npm run wp-env start',
		url: 'http://localhost:8888/wp-admin',
		reuseExistingServer: !process.env.CI,
		timeout: 120000,
	},
});
