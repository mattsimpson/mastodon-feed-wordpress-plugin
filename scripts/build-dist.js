#!/usr/bin/env node

/**
 * Mastodon Feed Production Build Script
 *
 * Creates a production-ready distribution of the plugin by:
 * - Copying only necessary files (no dev dependencies)
 * - Creating a ZIP file for easy WordPress plugin submission
 *
 * Usage: node scripts/build-dist.js
 */

const fs = require( 'fs' );
const path = require( 'path' );
const { execSync } = require( 'child_process' );

// Configuration
const ROOT_DIR = path.resolve( __dirname, '..' );
const packageJson = JSON.parse(
	fs.readFileSync( path.join( ROOT_DIR, 'package.json' ), 'utf8' )
);

const PLUGIN_NAME = packageJson.name;
const PLUGIN_VERSION = packageJson.version;
const DIST_DIR = path.join( ROOT_DIR, 'dist' );
const PLUGIN_DIR = path.join( DIST_DIR, PLUGIN_NAME );
const ZIP_NAME = `${ PLUGIN_NAME }-${ PLUGIN_VERSION }.zip`;

// Files and directories to include in distribution
const INCLUDE_PATTERNS = [
	// Root level files
	'mastodon-feed.php',
	'uninstall.php',
	'readme.txt',
	'LICENSE',

	// Directories to include entirely
	'build/',
	'languages/',
	'templates/',
];

// Patterns to explicitly exclude
const EXCLUDE_PATTERNS = [
	// Development files
	'node_modules',
	'vendor',
	'.git',
	'.github',
	'.claude',
	'.auth',
	'dist',
	'scripts',
	'src',
	'tests',
	'bin',
	'artifacts',
	'playwright-report',
	'test-results',
	'*.zip',

	// Config and build files
	'package.json',
	'package-lock.json',
	'composer.json',
	'composer.lock',
	'phpunit-unit.xml',
	'phpunit-integration.xml',
	'phpcs.xml',
	'phpstan.neon',
	'patchwork.json',
	'playwright.config.js',
	'.wp-env.json',

	// Documentation (not needed for distribution)
	'README.md',
	'CLAUDE.md',
	'config-example.php',

	// Version control
	'.gitignore',
	'.gitattributes',

	// Operating System Files (macOS)
	'.DS_Store',
	'._*',
	'.AppleDouble',
	'.LSOverride',
	'.Spotlight-V100',
	'.Trashes',

	// Operating System Files (Windows)
	'Thumbs.db',
	'ehthumbs.db',
	'Desktop.ini',
	'$RECYCLE.BIN',

	// Operating System Files (Linux)
	'.directory',
	'.Trash-*',

	// IDE and Editor Files
	'.idea',
	'.vscode',
	'.project',
	'.settings',
	'.buildpath',
	'*.swp',
	'*.swo',
	'*~',

	// Temporary and Log Files
	'*.log',
	'*.tmp',
	'*.temp',
	'*.bak',
	'*.cache',

	// Build artifacts
	'.sass-cache',
	'npm-debug.log*',
	'yarn-debug.log*',
	'yarn-error.log*',
];

/**
 * Recursively copy directory with filtering
 * @param src
 * @param dest
 * @param excludePatterns
 */
function copyDir( src, dest, excludePatterns = [] ) {
	if ( ! fs.existsSync( dest ) ) {
		fs.mkdirSync( dest, { recursive: true } );
	}

	const entries = fs.readdirSync( src, { withFileTypes: true } );

	for ( const entry of entries ) {
		const srcPath = path.join( src, entry.name );
		const destPath = path.join( dest, entry.name );

		// Check if this path should be excluded
		const shouldExclude = excludePatterns.some( ( pattern ) => {
			if ( pattern.endsWith( '/' ) ) {
				return (
					entry.name === pattern.slice( 0, -1 ) && entry.isDirectory()
				);
			}
			if ( pattern.includes( '*' ) ) {
				const regex = new RegExp(
					'^' + pattern.replace( '*', '.*' ) + '$'
				);
				return regex.test( entry.name );
			}
			return entry.name === pattern;
		} );

		if ( shouldExclude ) {
			continue;
		}

		if ( entry.isDirectory() ) {
			copyDir( srcPath, destPath, excludePatterns );
		} else {
			fs.copyFileSync( srcPath, destPath );
		}
	}
}

/**
 * Clean distribution directory
 */
function cleanDist() {
	console.log( '🧹 Cleaning distribution directory...' );
	if ( fs.existsSync( DIST_DIR ) ) {
		fs.rmSync( DIST_DIR, { recursive: true, force: true } );
	}
	fs.mkdirSync( PLUGIN_DIR, { recursive: true } );
}

/**
 * Copy plugin files to distribution
 */
function copyPluginFiles() {
	console.log( '📦 Copying plugin files...' );

	// Copy all files except excluded patterns
	copyDir( ROOT_DIR, PLUGIN_DIR, EXCLUDE_PATTERNS );

	console.log( '✅ Plugin files copied successfully' );
}

/**
 * Verify required files exist
 */
function verifyRequiredFiles() {
	console.log( '🔍 Verifying required files...' );

	const requiredFiles = [
		'mastodon-feed.php',
		'readme.txt',
		'build/block/index.js',
		'build/block/block.json',
	];

	const missingFiles = [];

	for ( const file of requiredFiles ) {
		const filePath = path.join( PLUGIN_DIR, file );
		if ( ! fs.existsSync( filePath ) ) {
			missingFiles.push( file );
		}
	}

	if ( missingFiles.length > 0 ) {
		console.error( '❌ Missing required files:' );
		missingFiles.forEach( ( file ) => console.error( `   - ${ file }` ) );
		console.error(
			'\n💡 Tip: Run "npm run build" before "npm run build:dist"'
		);
		process.exit( 1 );
	}

	console.log( '✅ All required files present' );
}

/**
 * Create ZIP archive
 */
function createZip() {
	console.log( '🗜️  Creating ZIP archive...' );

	const zipPath = path.join( ROOT_DIR, ZIP_NAME );

	// Remove old zip if exists
	if ( fs.existsSync( zipPath ) ) {
		fs.unlinkSync( zipPath );
	}

	try {
		// Use native zip command (works on macOS and Linux)
		process.chdir( DIST_DIR );
		execSync( `zip -r "${ zipPath }" ${ PLUGIN_NAME } -q`, {
			stdio: 'inherit',
		} );

		// Get file size
		const stats = fs.statSync( zipPath );
		const sizeInMB = ( stats.size / ( 1024 * 1024 ) ).toFixed( 2 );

		console.log(
			`✅ ZIP archive created: ${ ZIP_NAME } (${ sizeInMB } MB)`
		);
	} catch ( error ) {
		console.error( '❌ Failed to create ZIP archive:', error.message );
		console.log(
			'💡 Tip: Make sure the "zip" command is available on your system'
		);
		process.exit( 1 );
	}
}

/**
 * Display summary
 */
function displaySummary() {
	console.log( '\n' + '='.repeat( 60 ) );
	console.log( '🎉 Production build complete!' );
	console.log( '='.repeat( 60 ) );
	console.log( `📁 Distribution folder: ${ PLUGIN_DIR }` );
	console.log( `📦 ZIP file: ${ ZIP_NAME }` );
	console.log( '\n💡 Next steps:' );
	console.log(
		'   - Upload the ZIP file to WordPress via Plugins > Add New'
	);
	console.log( '   - Or extract the plugin folder to wp-content/plugins/' );
	console.log( '   - For WordPress.org submission, upload the ZIP file' );
	console.log( '='.repeat( 60 ) + '\n' );
}

/**
 * Main build process
 */
function main() {
	console.log( '\n🚀 Building Mastodon Feed production distribution...\n' );

	try {
		cleanDist();
		copyPluginFiles();
		verifyRequiredFiles();
		createZip();
		displaySummary();
	} catch ( error ) {
		console.error( '\n❌ Build failed:', error.message );
		process.exit( 1 );
	}
}

// Run the build
main();
