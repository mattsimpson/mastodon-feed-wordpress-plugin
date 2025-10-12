<?php
/**
 * PHPUnit bootstrap file for Mastodon Feed plugin tests
 */

// Require composer autoloader
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
	require dirname(__DIR__) . '/vendor/autoload.php';
}

$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
	// Use same logic as install script - check TMPDIR env var first
	$tmpdir = getenv('TMPDIR') ?: getenv('TEMP') ?: getenv('TMP') ?: sys_get_temp_dir();
	$tmpdir = rtrim($tmpdir, '/\\');
	$_tests_dir = $tmpdir . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file
$_phpunit_polyfills_path = getenv('WP_TESTS_PHPUNIT_POLYFILLS_PATH');
if (false !== $_phpunit_polyfills_path) {
	define('WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path);
}

if (!file_exists("{$_tests_dir}/includes/functions.php")) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit(1);
}

// Give access to tests_add_filter() function
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested
 */
function _manually_load_plugin() {
	require dirname(__DIR__) . '/mastodon-feed.php';
}

tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment
require "{$_tests_dir}/includes/bootstrap.php";
