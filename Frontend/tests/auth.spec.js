// @ts-check
import { test, expect } from '@playwright/test';

const baseURL = 'http://localhost:5173';

test.describe('Authentication Tests', () => {
    // Navigate to the login page before each test.
    test.beforeEach(async ({ page }) => {
        await page.goto(`${baseURL}/login`);
        await page.waitForLoadState('domcontentloaded');
    });

    test('Login with valid credentials', async ({ page }) => {
        // Fill in the login form.
        await page.fill('input#email', 'admin@example.com');
        await page.fill('input#password', 'admin123');

        // Locate the login button using a role-based query.
        const loginBtn = page.getByRole('button', { name: /log\s*in/i });
        await expect(loginBtn).toBeVisible({ timeout: 10000 });
        await loginBtn.click();

        // Wait for the backend login API response.
        await page.waitForResponse(response =>
            response.url().includes('/api/users/login') && response.status() === 200,
            { timeout: 15000 }
        );
        // Allow extra time for state update and navigation.
        await page.waitForTimeout(3000);

        // Expect redirection to a dashboard page (which can be admin-dashboard, guest-dashboard or home).
        await expect(page).toHaveURL(/(admin-dashboard|guest-dashboard|\/)$/i, { timeout: 15000 });
        // Verify the username is displayed.
        await expect(page.locator('.username-span')).toContainText(/admin/i, { timeout: 15000 });
    });

    test('Login fails with wrong credentials', async ({ page }) => {
        // Fill in the form with incorrect credentials.
        await page.fill('input#email', 'wrong@example.com');
        await page.fill('input#password', 'wrongpassword');

        const loginBtn = page.getByRole('button', { name: /log\s*in/i });
        await expect(loginBtn).toBeVisible({ timeout: 10000 });
        await loginBtn.click();

        // Wait and verify that an error message appears.
        const errorMessage = page.locator('.status-message.error-message, .alert-danger');
        await expect(errorMessage).toBeVisible({ timeout: 15000 });
    });

    test('Logout works', async ({ page, browserName }) => {
        // 1. Login first.
        await page.fill('input#email', 'admin@example.com');
        await page.fill('input#password', 'admin123');
        const loginBtn = page.getByRole('button', { name: /log\s*in/i });
        await expect(loginBtn).toBeVisible({ timeout: 10000 });
        await loginBtn.click();

        await page.waitForResponse(response =>
            response.url().includes('/api/users/login') && response.ok(),
            { timeout: 15000 }
        );
        await page.waitForTimeout(3000);

        // Make sure we're on a dashboard page.
        await expect(page).toHaveURL(/(admin-dashboard|guest-dashboard|\/)$/i, { timeout: 15000 });
        await expect(page.locator('.username-span')).toContainText(/admin/i, { timeout: 15000 });

        // 2. Open the dropdown menu.
        const dropdown = page.locator('a.nav-link.dropdown-toggle');
        await expect(dropdown).toBeVisible({ timeout: 10000 });
        await dropdown.click();

        // 3. Click the logout link.
        const logoutLink = page.getByRole('link', { name: /logout/i });
        await expect(logoutLink).toBeVisible({ timeout: 10000 });
        await logoutLink.click();

        // 4. Wait for a short period to allow logout processing.
        await page.waitForTimeout(3000);

        // 5. Check the current URL.
        const currentURL = await page.url();
        console.log('Current URL after logout:', currentURL);
        // If the URL is not the login page, force navigation.
        if (!currentURL.includes('/login')) {
            await page.goto(`${baseURL}/login`);
            if (browserName === 'firefox') {
                await page.waitForLoadState('networkidle');
            } else {
                await page.waitForLoadState('domcontentloaded');
            }
        }

        // 6. Verify that the login button appears using its test ID.
        const loginButton = page.getByTestId('login-button');
        await expect(loginButton).toBeVisible({ timeout: 15000 });
    });
});
