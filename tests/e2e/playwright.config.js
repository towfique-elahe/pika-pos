/**
 * End-to-end tests against the running WordPress Studio site.
 *
 * These drive the real terminal against real WooCommerce data, so every run
 * creates real orders in the local database. That is the point — a POS that
 * passes unit tests and fails at the counter is worthless.
 */
const { defineConfig, devices } = require( '@playwright/test' );

const baseURL = process.env.PIKA_POS_SITE_URL || 'http://localhost:8883';

module.exports = defineConfig( {
	testDir: __dirname,
	timeout: 60_000,
	expect: { timeout: 10_000 },
	fullyParallel: false,
	workers: 1,
	retries: 0,
	reporter: [ [ 'list' ] ],
	outputDir: `${ __dirname }/test-results`,
	use: {
		baseURL,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		video: 'off',
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices[ 'Desktop Chrome' ], viewport: { width: 1440, height: 900 } },
		},
	],
} );
