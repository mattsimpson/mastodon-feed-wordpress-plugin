<?php
/**
 * Integration tests for REST API endpoints
 *
 * @package MastodonFeed
 */

namespace IncludeMastodonFeedPlugin\Tests\Integration;

use WP_REST_Request;
use WP_UnitTestCase;

/**
 * Test REST API functionality
 */
class Test_REST_API extends WP_UnitTestCase {

	protected $server;
	protected $editor_user;

	public function setUp(): void {
		parent::setUp();

		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server();
		do_action('rest_api_init');

		// Create editor user for permission tests
		$this->editor_user = $this->factory->user->create([
			'role' => 'editor',
		]);
	}

	/**
	 * Test REST API endpoint is registered
	 */
	public function test_endpoint_is_registered() {
		$routes = $this->server->get_routes();
		$this->assertArrayHasKey('/mastodon-feed/v1/lookup-account', $routes);
	}

	/**
	 * Test endpoint requires authentication
	 */
	public function test_endpoint_requires_authentication() {
		$request = new WP_REST_Request('POST', '/mastodon-feed/v1/lookup-account');
		$request->set_param('handle', 'test@mastodon.social');

		$response = $this->server->dispatch($request);

		$this->assertEquals(401, $response->get_status());
	}

	/**
	 * Test endpoint requires handle parameter
	 */
	public function test_endpoint_requires_handle_parameter() {
		wp_set_current_user($this->editor_user);

		$request = new WP_REST_Request('POST', '/mastodon-feed/v1/lookup-account');
		$response = $this->server->dispatch($request);

		$this->assertEquals(400, $response->get_status());
	}

	/**
	 * Test endpoint validates handle format
	 */
	public function test_endpoint_validates_handle_format() {
		wp_set_current_user($this->editor_user);

		$request = new WP_REST_Request('POST', '/mastodon-feed/v1/lookup-account');
		$request->set_param('handle', 'invalid-handle');

		$response = $this->server->dispatch($request);
		$data = $response->get_data();

		$this->assertFalse($response->is_error() === false); // Expect error
		$this->assertArrayHasKey('code', $data);
	}

	/**
	 * Test editor can use endpoint
	 */
	public function test_editor_can_use_endpoint() {
		wp_set_current_user($this->editor_user);

		$request = new WP_REST_Request('POST', '/mastodon-feed/v1/lookup-account');
		$request->set_param('handle', 'test@mastodon.social');

		// This will fail at API call, but permission check passes
		$response = $this->server->dispatch($request);

		// Should not be 401/403 (auth error)
		$this->assertNotEquals(401, $response->get_status());
		$this->assertNotEquals(403, $response->get_status());
	}
}
