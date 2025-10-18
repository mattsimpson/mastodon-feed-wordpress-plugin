module.exports = {
	rootDir: '../../',
	preset: '@wordpress/jest-preset-default',
	testMatch: [ '**/tests/js/**/*.test.[jt]s?(x)' ],
	transform: {
		'^.+\\.[jt]sx?$':
			'<rootDir>/node_modules/@wordpress/scripts/config/babel-transform',
	},
	setupFilesAfterEnv: [ '<rootDir>/tests/js/setup-tests.js' ],
	testPathIgnorePatterns: [ '/node_modules/', '/build/', '/vendor/' ],
	coveragePathIgnorePatterns: [ '/node_modules/', '/build/', '/vendor/' ],
	transformIgnorePatterns: [ 'node_modules/(?!(parsel-js)/)' ],
	moduleNameMapper: {
		'\\.(css|less|scss|sass)$': '<rootDir>/tests/js/__mocks__/styleMock.js',
	},
};
