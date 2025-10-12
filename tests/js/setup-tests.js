/**
 * Jest setup file
 * Runs before each test file
 */

import '@testing-library/jest-dom';

// Mock WordPress dependencies
global.wp = {
	apiFetch: jest.fn(),
	i18n: {
		__: (text) => text,
		_x: (text) => text,
		_n: (single, plural, number) => (number === 1 ? single : plural),
		sprintf: (format, ...args) => {
			let formatted = format;
			args.forEach((arg, i) => {
				formatted = formatted.replace(`%${i + 1}$s`, arg).replace('%s', arg).replace('%d', arg);
			});
			return formatted;
		},
	},
	element: require('@wordpress/element'),
	components: require('@wordpress/components'),
	blockEditor: require('@wordpress/block-editor'),
	blocks: require('@wordpress/blocks'),
	data: require('@wordpress/data'),
};

// Mock console methods to keep test output clean
global.console = {
	...console,
	error: jest.fn(),
	warning: jest.fn(),
};
