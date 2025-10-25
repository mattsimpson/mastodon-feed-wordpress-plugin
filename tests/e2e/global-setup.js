/**
 * Global setup for E2E tests
 * Handles WordPress authentication before running tests
 */

const { request } = require( '@playwright/test' );
const { RequestUtils } = require( '@wordpress/e2e-test-utils-playwright' );

/**
 * Authenticate with WordPress and save storage state
 *
 * @param {import('@playwright/test').FullConfig} config - Playwright configuration
 * @return {Promise<void>}
 */
async function globalSetup( config ) {
	const { storageState, baseURL } = config.projects[ 0 ].use;
	const storageStatePath =
		typeof storageState === 'string' ? storageState : undefined;

	console.log( 'Setting up WordPress authentication via REST API...' );

	const requestContext = await request.newContext( {
		baseURL,
	} );

	const requestUtils = new RequestUtils( requestContext, {
		storageStatePath,
	} );

	// Authenticate and save the storageState to disk.
	await requestUtils.setupRest();

	// Note: Plugin is automatically activated by wp-env when using "plugins": ["."]
	// No manual activation needed

	await requestContext.dispose();

	console.log( 'WordPress authentication successful!' );
}

module.exports = globalSetup;
