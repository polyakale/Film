// @ts-check
import { test, expect } from '@playwright/test';

const url = 'http://localhost:5173';

test.describe('Authentication Tests', () => {
    test('Login with valid credentials', async ({ page }) => {
        await page.goto(`${url}/login`);
        await page.waitForLoadState('domcontentloaded');

        await page.fill('input#email', 'test@example.com');
        await page.fill('input#password', '123');

        // More robust selector
        const loginBtn = page.locator('button[type="submit"]');
        await expect(loginBtn).toBeVisible({ timeout: 10000 });
        await loginBtn.click();

        await expect(page).toHaveURL(url + '/');
        await expect(page.locator('.username-span')).toContainText(/test/i);
    });

    test('Login fails with wrong credentials', async ({ page }) => {
        await page.goto(`${url}/login`);

        await page.fill('input#email', 'wrong@example.com');
        await page.fill('input#password', 'wrongpassword');

        const loginBtn = page.getByRole('button', { name: /login/i });
        await expect(loginBtn).toBeVisible({ timeout: 10000 });
        await loginBtn.click();

        // Adjust selector to your actual error message element
        await expect(page.locator('.alert-danger')).toBeVisible();
    });

    test('Logout works', async ({ page }) => {
        await page.goto(`${url}/login`);
        await page.fill('input#email', 'test@example.com');
        await page.fill('input#password', '123');
        await page.getByRole('button', { name: /login/i }).click();

        // Click dropdown to reveal logout
        await page.click('a#navbarDropdown');
        await page.click('a:has-text("Logout")');

        await expect(page.locator('.username-span')).toHaveText('Account');
    });
});
