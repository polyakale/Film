import { test, expect } from '@playwright/test';

const url = 'http://localhost:5173';

test.describe('Home Page Tests', () => {
  test('Home loads with correct title and visible content', async ({ page }) => {
    await page.goto(url);

    // Cím rendben
    await expect(page).toHaveTitle(/Old Hungarian Films/i);

    // Keress specifikusabb elemet (pl. főcím)
    await expect(page.locator('#app')).toContainText(/Hungarian Interwar Film Archive/i);
  });

  test('Navigation bar is visible and working', async ({ page }) => {
    await page.goto(`${url}/`);

    await expect(page.locator('nav')).toBeVisible();

    // Kattints a dropdown toggle-re, hogy Login látható legyen
    await page.click('a.nav-link.dropdown-toggle');
    await expect(page.locator('a:has-text("Login")')).toBeVisible({ timeout: 10000 });

    // A Films menüpont nem sima <a>, hanem valószínűleg Vue RouterLink → span-eken belül van a szöveg
    await expect(page.locator('span.nav-link-text', { hasText: 'Films' })).toBeVisible({ timeout: 5000 });

    // Kattints a szülő elemre, ne a span-re
    await page.locator('span.nav-link-text', { hasText: 'Films' }).click();

    await expect(page).toHaveURL(`${url}/films`);
  });

  test('Mobile menu toggles on small screens', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto(url);

    const toggler = page.locator('.navbar-toggler');
    await expect(toggler).toBeVisible();
    await toggler.click();

    // Most már kattintható dropdown menü
    const dropdownToggle = page.locator('a.nav-link.dropdown-toggle');
    await dropdownToggle.click();

    await expect(page.locator('a:has-text("Login")')).toBeVisible({ timeout: 10000 });
  });
});
