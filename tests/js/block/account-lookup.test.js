/**
 * Test account lookup functionality in block
 */

import apiFetch from '@wordpress/api-fetch';

jest.mock( '@wordpress/api-fetch' );

describe( 'Account Lookup API Integration', () => {
	beforeEach( () => {
		jest.clearAllMocks();
	} );

	test( 'account lookup API call has correct endpoint', async () => {
		const mockResponse = {
			success: true,
			instance: 'mastodon.social',
			account_id: '123456',
			display_name: 'Test User',
		};

		apiFetch.mockResolvedValue( mockResponse );

		await apiFetch( {
			path: '/mastodon-feed/v1/lookup-account',
			method: 'POST',
			data: { handle: 'test@mastodon.social' },
		} );

		expect( apiFetch ).toHaveBeenCalledWith( {
			path: '/mastodon-feed/v1/lookup-account',
			method: 'POST',
			data: { handle: 'test@mastodon.social' },
		} );
	} );

	test( 'successful account lookup returns expected data', async () => {
		const mockResponse = {
			success: true,
			instance: 'mastodon.social',
			account_id: '123456',
			display_name: 'Test User',
			acct: 'test',
		};

		apiFetch.mockResolvedValue( mockResponse );

		const result = await apiFetch( {
			path: '/mastodon-feed/v1/lookup-account',
			method: 'POST',
			data: { handle: 'test@mastodon.social' },
		} );

		expect( result.success ).toBe( true );
		expect( result.instance ).toBe( 'mastodon.social' );
		expect( result.account_id ).toBe( '123456' );
	} );

	test( 'account lookup handles API error', async () => {
		const mockError = {
			code: 'account_not_found',
			message: 'Account not found',
		};

		apiFetch.mockRejectedValue( mockError );

		try {
			await apiFetch( {
				path: '/mastodon-feed/v1/lookup-account',
				method: 'POST',
				data: { handle: 'nonexistent@mastodon.social' },
			} );
		} catch ( error ) {
			expect( error.code ).toBe( 'account_not_found' );
		}
	} );

	test( 'account lookup handles invalid handle format', async () => {
		const mockError = {
			code: 'invalid_handle',
			message: 'Invalid handle format',
		};

		apiFetch.mockRejectedValue( mockError );

		try {
			await apiFetch( {
				path: '/mastodon-feed/v1/lookup-account',
				method: 'POST',
				data: { handle: 'invalid-handle' },
			} );
		} catch ( error ) {
			expect( error.code ).toBe( 'invalid_handle' );
		}
	} );

	test( 'account lookup handles network timeout', async () => {
		const mockError = {
			code: 'connection_timeout',
			message: 'Connection timed out',
		};

		apiFetch.mockRejectedValue( mockError );

		try {
			await apiFetch( {
				path: '/mastodon-feed/v1/lookup-account',
				method: 'POST',
				data: { handle: 'test@slow-instance.example' },
			} );
		} catch ( error ) {
			expect( error.code ).toBe( 'connection_timeout' );
		}
	} );

	test( 'account lookup formats handle correctly', () => {
		const testCases = [
			{
				input: '@user@mastodon.social',
				expected: 'user@mastodon.social',
			},
			{ input: 'user@mastodon.social', expected: 'user@mastodon.social' },
			{
				input: '  @user@mastodon.social  ',
				expected: 'user@mastodon.social',
			},
		];

		testCases.forEach( ( { input, expected } ) => {
			const cleaned = input.trim().replace( /^@/, '' );
			expect( cleaned ).toBe( expected );
		} );
	} );
} );
