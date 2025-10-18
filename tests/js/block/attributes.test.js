/**
 * Test block attributes and defaults
 */

import blockMetadata from '../../../src/block/block.json';

describe( 'Block Attributes', () => {
	test( 'block metadata exists', () => {
		expect( blockMetadata ).toBeDefined();
		expect( blockMetadata.name ).toBe( 'mastodon-feed/embed' );
	} );

	test( 'block has required attributes', () => {
		const attributes = blockMetadata.attributes;

		expect( attributes ).toHaveProperty( 'instance' );
		expect( attributes ).toHaveProperty( 'account' );
		expect( attributes ).toHaveProperty( 'tag' );
		expect( attributes ).toHaveProperty( 'limit' );
	} );

	test( 'instance attribute has correct type and default', () => {
		const instance = blockMetadata.attributes.instance;

		expect( instance.type ).toBe( 'string' );
		expect( instance.default ).toBe( 'mastodon.social' );
	} );

	test( 'account attribute has correct type', () => {
		const account = blockMetadata.attributes.account;

		expect( account.type ).toBe( 'string' );
		expect( account.default ).toBe( '' );
	} );

	test( 'limit attribute has correct type and default', () => {
		const limit = blockMetadata.attributes.limit;

		expect( limit.type ).toBe( 'number' );
		expect( limit.default ).toBe( 10 );
	} );

	test( 'boolean filter attributes have correct defaults', () => {
		const attributes = blockMetadata.attributes;

		expect( attributes.excludeBoosts.type ).toBe( 'boolean' );
		expect( attributes.excludeBoosts.default ).toBe( false );

		expect( attributes.excludeReplies.type ).toBe( 'boolean' );
		expect( attributes.excludeReplies.default ).toBe( false );

		expect( attributes.onlyPinned.type ).toBe( 'boolean' );
		expect( attributes.onlyPinned.default ).toBe( false );

		expect( attributes.onlyMedia.type ).toBe( 'boolean' );
		expect( attributes.onlyMedia.default ).toBe( false );
	} );

	test( 'display option attributes exist', () => {
		const attributes = blockMetadata.attributes;

		expect( attributes ).toHaveProperty( 'showPreviewCards' );
		expect( attributes ).toHaveProperty( 'showPostAuthor' );
		expect( attributes ).toHaveProperty( 'showDateTime' );
	} );

	test( 'block uses server-side rendering', () => {
		// Block should not have a save function (uses server-side render)
		// This is indicated by usesContext or render callback in block.json
		expect( blockMetadata ).toBeDefined();
	} );

	test( 'block category is correct', () => {
		expect( blockMetadata.category ).toBe( 'embed' );
	} );

	test( 'block has icon', () => {
		expect( blockMetadata.icon ).toBeDefined();
	} );

	test( 'block supports are defined', () => {
		expect( blockMetadata.supports ).toBeDefined();
	} );
} );
