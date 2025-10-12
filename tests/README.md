# Mastodon Feed Plugin - Testing Guide

This document explains how to run the comprehensive test suite for the Mastodon Feed WordPress plugin.

## Table of Contents

- [Setup](#setup)
- [Running Tests](#running-tests)
- [Test Types](#test-types)
- [Continuous Integration](#continuous-integration)
- [Writing Tests](#writing-tests)

## Setup

### PHP Tests Setup

1. **Install Composer dependencies:**
   ```bash
   composer install
   ```

2. **Install WordPress test suite:**
   ```bash
   bash bin/install-wp-tests.sh wordpress_test root 'your-password' 127.0.0.1 latest
   ```

   Parameters:
   - `wordpress_test` - Database name for tests
   - `root` - MySQL username
   - `'your-password'` - MySQL password (use quotes!)
   - `127.0.0.1` - MySQL host
   - `latest` - WordPress version (or specific like `6.4`)

### JavaScript Tests Setup

1. **Install npm dependencies:**
   ```bash
   npm install
   ```

2. **For E2E tests, install Playwright browsers:**
   ```bash
   npx playwright install --with-deps
   ```

## Running Tests

### All Tests

Run the complete test suite:
```bash
composer test:all  # PHP tests + code quality
npm test           # JavaScript tests
```

### PHP Unit Tests

Test individual functions with mocked dependencies:
```bash
composer test:unit
```

### PHP Integration Tests

Test WordPress integration with real WP environment:
```bash
composer test:integration
```

### PHP Code Quality

```bash
composer phpcs      # Code standards check
composer phpcbf     # Auto-fix code standards
composer phpstan    # Static analysis
```

### JavaScript Unit Tests

```bash
npm test            # Run Jest tests
npm run test:watch  # Watch mode for development
```

### E2E Tests

Test the block in actual WordPress environment:

1. **Start WordPress environment:**
   ```bash
   npm run wp-env:start
   ```

2. **Run E2E tests:**
   ```bash
   npm run test:e2e           # Headless
   npm run test:e2e:headed    # With browser visible
   ```

3. **Stop environment:**
   ```bash
   npm run wp-env:stop
   ```

## Test Types

### PHP Unit Tests (`tests/php/unit/`)

- **test-fetch-cache.php** - API fetching and caching logic
- **test-account-lookup.php** - Account lookup functionality
- **test-validation.php** - Input validation functions
- **test-sanitization.php** - Security and sanitization
- **test-render-functions.php** - Admin field rendering
- **test-shortcode.php** - Shortcode functionality
- **test-block-render.php** - Block render callback
- **test-css-generation.php** - CSS generation

### PHP Integration Tests (`tests/php/integration/`)

- **test-settings.php** - WordPress settings API integration
- **test-rest-api.php** - REST API endpoints
- **test-block-registration.php** - Gutenberg block registration
- **test-admin-page.php** - Admin interface
- **test-uninstall.php** - Cleanup on uninstall

### JavaScript Tests (`tests/js/block/`)

- **edit.test.js** - Block edit component
- **attributes.test.js** - Block attributes and defaults
- **account-lookup.test.js** - Account lookup API integration

### E2E Tests (`tests/e2e/`)

- **block-editor.spec.js** - Full block editor workflows

## Continuous Integration

### GitHub Actions

The plugin uses GitHub Actions for automated testing on every push and PR:

#### Test Workflow (`.github/workflows/tests.yml`)

Runs on: Push to main/develop, Pull Requests

**Matrix Testing:**
- PHP versions: 7.4, 8.0, 8.1, 8.2
- WordPress versions: 6.0, 6.3, 6.4, latest

**Jobs:**
1. **PHPUnit** - Unit and integration tests
2. **PHPCS** - Code standards
3. **PHPStan** - Static analysis
4. **JavaScript** - ESLint + Jest tests + Build
5. **E2E** - Playwright tests with wp-env

#### Deploy Workflow (`.github/workflows/deploy.yml`)

Runs on: Git tag push (e.g., `v1.0.1`)

**Steps:**
1. Build production assets
2. Deploy to WordPress.org SVN
3. Create GitHub release with ZIP

### Required GitHub Secrets

For deployment to work, add these secrets in your GitHub repository settings:
- `SVN_USERNAME` - WordPress.org username
- `SVN_PASSWORD` - WordPress.org password

## Writing Tests

### PHP Unit Test Example

```php
<?php
namespace IncludeMastodonFeedPlugin\Tests\Unit;

use Brain\Monkey;
use Brain\Monkey\Functions;
use PHPUnit\Framework\TestCase;

class Test_My_Feature extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function test_my_function() {
        Functions\expect('get_option')->andReturn('value');

        require_once __DIR__ . '/../../../mastodon-feed.php';

        $result = \IncludeMastodonFeedPlugin\my_function();

        $this->assertEquals('expected', $result);
    }
}
```

### PHP Integration Test Example

```php
<?php
namespace IncludeMastodonFeedPlugin\Tests\Integration;

use WP_UnitTestCase;

class Test_My_Integration extends WP_UnitTestCase {

    public function test_option_is_saved() {
        update_option('mastodon_feed_test', 'value');

        $this->assertEquals('value', get_option('mastodon_feed_test'));
    }
}
```

### JavaScript Test Example

```javascript
import { render, screen } from '@testing-library/react';

describe('My Component', () => {
    test('renders correctly', () => {
        render(<MyComponent />);
        expect(screen.getByText('Hello')).toBeInTheDocument();
    });
});
```

### E2E Test Example

```javascript
const { test, expect } = require('@playwright/test');

test('can insert block', async ({ page }) => {
    await admin.visitAdminPage('post-new.php');
    await editor.insertBlock({ name: 'mastodon-feed/embed' });

    const block = page.locator('[data-type="mastodon-feed/embed"]');
    await expect(block).toBeVisible();
});
```

## Test Coverage

To generate code coverage reports (requires Xdebug):

```bash
composer test -- --coverage-html coverage/
```

Open `coverage/index.html` in your browser to view the report.

## Local Development Environment

Use `wp-env` for consistent local testing:

```bash
npm run wp-env:start    # Start WordPress
npm run wp-env:stop     # Stop WordPress
npm run wp-env:destroy  # Remove everything
```

Access:
- WordPress: http://localhost:8888
- Admin: http://localhost:8888/wp-admin (admin/password)

## Troubleshooting

### PHPUnit Issues

**Problem:** `WP_TESTS_DIR` not found
```bash
# Solution: Set the environment variable
export WP_TESTS_DIR=/tmp/wordpress-tests-lib
```

**Problem:** Database connection failed
```bash
# Solution: Check MySQL is running and credentials are correct
mysql -u root -p -e "SHOW DATABASES;"
```

### Playwright Issues

**Problem:** Browsers not installed
```bash
# Solution: Install browsers
npx playwright install --with-deps
```

**Problem:** wp-env won't start
```bash
# Solution: Clean up and restart
npm run wp-env:destroy
npm run wp-env:start
```

## Best Practices

1. **Run tests before committing** - Ensure all tests pass locally
2. **Write tests for new features** - Maintain test coverage
3. **Test edge cases** - Don't just test the happy path
4. **Use descriptive test names** - Make failures easy to understand
5. **Keep tests independent** - Tests should not depend on each other
6. **Mock external APIs** - Don't make real Mastodon API calls in tests

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress Testing Handbook](https://make.wordpress.org/core/handbook/testing/)
- [Jest Documentation](https://jestjs.io/docs/getting-started)
- [Playwright Documentation](https://playwright.dev/docs/intro)
- [Brain Monkey](https://giuseppe-mazzapica.gitbook.io/brain-monkey/)
