module.exports = {
	extends: [ 'plugin:@wordpress/eslint-plugin/recommended' ],
	globals: {
		jQuery: 'readonly',
		wp: 'readonly',
	},
	env: {
		browser: true,
		node: true,
		jest: true,
	},
	rules: {
		// Allow console statements in build scripts and tests
		'no-console': [
			'error',
			{
				allow: [ 'warn', 'error', 'log' ],
			},
		],
		// Allow conditional expect in tests (sometimes necessary)
		'jest/no-conditional-expect': 'off',
	},
	overrides: [
		{
			// Build scripts
			files: [ 'scripts/**/*.js' ],
			rules: {
				'jsdoc/require-param-type': 'off',
				'no-unused-vars': [
					'error',
					{ varsIgnorePattern: 'INCLUDE_PATTERNS' },
				],
			},
		},
		{
			// Test files
			files: [ 'tests/**/*.js', '**/*.test.js' ],
			rules: {
				'no-unused-vars': [
					'error',
					{
						varsIgnorePattern: 'render|screen|fireEvent|waitFor',
					},
				],
			},
		},
	],
};
