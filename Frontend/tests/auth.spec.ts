// tests/auth.spec.ts
import { test, expect } from '@playwright/test';

const url = 'http://localhost:5173';

test.describe('Authentication Tests', () => {
    // Navigate to the login page before each test
    test.beforeEach(async ({ page }) => {
        await page.goto(`${url}/login`);
    });

    test('Login with valid credentials', async ({ page }) => {
        // Use the default/test credentials defined in your Login.vue
        await page.fill('input#email', 'admin@example.com');
        await page.fill('input#password', 'admin123');

        // Locate the login button. It’s recommended to use a role-based query;
        // adjust if you add an explicit aria-label in your component.
        const loginBtn = page.getByRole('button', { name: /log\s*in/i });
        await expect(loginBtn).toBeVisible({ timeout: 10000 });
        await loginBtn.click();

        // Expect a redirect to /admin-dashboard or /guest-dashboard.
        await expect(page).toHaveURL(/(admin-dashboard|guest-dashboard)/, { timeout: 10000 });

        // Optionally, check for a username element that confirms login.
        await expect(page.locator('.username-span')).toContainText(/admin/i, { timeout: 10000 });
    });

    test('Login fails with wrong credentials', async ({ page }) => {
        // Fill in wrong credentials
        await page.fill('input#email', 'wrong@example.com');
        await page.fill('input#password', 'wrongpassword');

        const loginBtn = page.getByRole('button', { name: /log\s*in/i });
        await expect(loginBtn).toBeVisible({ timeout: 10000 });
        await loginBtn.click();

        // Expect an error message to appear.
        await expect(page.locator('.status-message.error-message')).toBeVisible({ timeout: 10000 });
    });

    test('Logout works', async ({ page }) => {
        // Log in using valid credentials.
        await page.fill('input#email', 'admin@example.com');
        await page.fill('input#password', 'admin123');
        const loginBtn = page.getByRole('button', { name: /log\s*in/i });
        await expect(loginBtn).toBeVisible({ timeout: 10000 });
        await loginBtn.click();

        // Wait for redirection to the dashboard.
        await expect(page).toHaveURL(/(admin-dashboard|guest-dashboard)/, { timeout: 10000 });

        // *** IMPORTANT ***
        // Confirm from your browser's developer tools which selector corresponds 
        // to the dropdown or user menu that opens the logout option.
        // For example, if it’s a button with aria-label "user menu", use:
        // const userMenuBtn = page.getByRole('button', { name: /user menu/i });

        // Here we're still using 'a#navbarDropdown', so update this if needed.
        await page.waitForSelector('a#navbarDropdown', { state: 'visible', timeout: 10000 });
        await page.click('a#navbarDropdown');

        // Click the logout link.
        await page.waitForSelector('a#logout', { state: 'visible', timeout: 10000 });
        await page.click('a#logout');

        // Verify that the page redirects back to the login page.
        await expect(page).toHaveURL(`${url}/login`, { timeout: 10000 });
        await expect(page.getByRole('button', { name: /log\s*in/i })).toBeVisible({ timeout: 10000 });
    });
});
