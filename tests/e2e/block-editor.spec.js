/**
 * E2E tests for Mastodon Feed block in WordPress block editor
 */

const { test, expect } = require('@wordpress/e2e-test-utils-playwright');

test.describe('Mastodon Feed Block E2E', () => {
	test.beforeEach(async ({ admin }) => {
		await admin.visitAdminPage('post-new.php');
	});

	test('can insert Mastodon Feed block', async ({ editor }) => {
		// Insert block
		await editor.insertBlock({ name: 'mastodon-feed/embed' });

		// Check block is inserted (blocks are in the editor canvas iframe)
		const block = editor.canvas.locator('[data-type="mastodon-feed/embed"]');
		await expect(block).toBeVisible();
	});

	test('block shows validation error without account or tag', async ({ editor }) => {
		// Insert block
		await editor.insertBlock({ name: 'mastodon-feed/embed' });

		// Block should show the setup form (no error, just setup instructions)
		const setupText = editor.canvas.locator('text=/Enter your Mastodon account ID/i');
		await expect(setupText).toBeVisible({ timeout: 10000 });
	});

	test('can configure block with account ID', async ({ editor }) => {
		// Insert block
		await editor.insertBlock({ name: 'mastodon-feed/embed' });

		// Fill in instance and account (forms are in the canvas)
		await editor.canvas.getByLabel('INSTANCE').fill('mastodon.social');
		await editor.canvas.getByLabel('ACCOUNT ID').fill('123456');

		// Check settings are applied
		await expect(editor.canvas.getByLabel('INSTANCE')).toHaveValue('mastodon.social');
		await expect(editor.canvas.getByLabel('ACCOUNT ID')).toHaveValue('123456');
	});

	test('can configure block with tag', async ({ editor }) => {
		// Insert block
		await editor.insertBlock({ name: 'mastodon-feed/embed' });

		// Fill in instance and tag (forms are in the canvas)
		await editor.canvas.getByLabel('INSTANCE').fill('mastodon.social');
		await editor.canvas.getByLabel('TAG').fill('photography');

		// Check settings are applied
		await expect(editor.canvas.getByLabel('TAG')).toHaveValue('photography');
	});

	test('can toggle filter options', async ({ editor, page }) => {
		// Insert and configure block (Inspector Controls only appear after configuration)
		await editor.insertBlock({ name: 'mastodon-feed/embed' });
		await editor.canvas.getByLabel('INSTANCE').fill('mastodon.social');
		const accountField = editor.canvas.getByLabel('ACCOUNT ID');
		await accountField.fill('123456');
		await accountField.blur(); // Trigger onBlur to finalize setup

		// Wait for block to switch from placeholder to feed preview
		await page.waitForTimeout(1000); // Wait for ServerSideRender to load

		// Find and click filter panel in sidebar
		const filterPanel = page.getByRole('button', { name: /filter options/i });
		await filterPanel.click();

		// Toggle exclude boosts
		const excludeBoostsToggle = page.getByLabel(/exclude.*boost/i);
		await excludeBoostsToggle.check();
		await expect(excludeBoostsToggle).toBeChecked();
	});

	test('can toggle display options', async ({ editor, page }) => {
		// Insert and configure block (Inspector Controls only appear after configuration)
		await editor.insertBlock({ name: 'mastodon-feed/embed' });
		await editor.canvas.getByLabel('INSTANCE').fill('mastodon.social');
		const accountField = editor.canvas.getByLabel('ACCOUNT ID');
		await accountField.fill('123456');
		await accountField.blur(); // Trigger onBlur to finalize setup

		// Wait for block to switch from placeholder to feed preview
		await page.waitForTimeout(1000); // Wait for ServerSideRender to load

		// Find and click display options panel in sidebar
		const displayPanel = page.getByRole('button', { name: /display options/i });
		await displayPanel.click();

		// Toggle show author
		const showAuthorToggle = page.getByLabel(/show.*author/i);
		await showAuthorToggle.uncheck();
		await expect(showAuthorToggle).not.toBeChecked();
	});

	test('account lookup panel is available', async ({ editor }) => {
		// Insert block
		await editor.insertBlock({ name: 'mastodon-feed/embed' });

		// Check account lookup button/form exists in the block
		const lookupButton = editor.canvas.getByRole('button', { name: /lookup account id/i });
		await expect(lookupButton).toBeVisible();
	});

	test('can save post with block', async ({ editor, page }) => {
		// Insert block with valid configuration
		await editor.insertBlock({ name: 'mastodon-feed/embed' });

		// Configure block (forms are in the canvas)
		await editor.canvas.getByLabel('INSTANCE').fill('mastodon.social');
		await editor.canvas.getByLabel('ACCOUNT ID').fill('123456');

		// Save post
		await editor.publishPost();

		// Check post is published (use first match to avoid strict mode violation)
		const publishedNotice = page.locator('text=/published/i').first();
		await expect(publishedNotice).toBeVisible({ timeout: 10000 });
	});

	test('block persists settings after reload', async ({ editor, page }) => {
		// Insert and configure block
		await editor.insertBlock({ name: 'mastodon-feed/embed' });
		await editor.canvas.getByLabel('INSTANCE').fill('fosstodon.org');
		const accountField = editor.canvas.getByLabel('ACCOUNT ID');
		await accountField.fill('999999');
		await accountField.blur(); // Trigger onBlur to finalize setup

		// Wait for feed to render
		await page.waitForTimeout(1000); // Wait for ServerSideRender to load

		// Save as draft
		await page.getByRole('button', { name: /save draft/i }).click();
		await page.locator('text=/saved/i').first().waitFor({ timeout: 10000 });

		// Reload page
		await page.reload();

		// Wait for editor and block to load
		await editor.canvas.locator('[data-type="mastodon-feed/embed"]').waitFor();

		// Select the block to show inspector controls in sidebar
		await editor.canvas.locator('[data-type="mastodon-feed/embed"]').click();
		await page.waitForTimeout(500);

		// Check block settings persisted (check in Feed Settings panel in inspector)
		const instanceInput = page.getByRole('textbox', { name: /instance/i }).first();
		await expect(instanceInput).toHaveValue('fosstodon.org');

		const accountInput = page.getByRole('textbox', { name: /account id/i }).first();
		await expect(accountInput).toHaveValue('999999');
	});
});
