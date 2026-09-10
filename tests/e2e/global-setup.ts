import { chromium, type FullConfig } from '@playwright/test';
import { mkdirSync } from 'node:fs';

const AUTH_FILE = 'artifacts/.auth/user.json';

async function globalSetup(_config: FullConfig) {
    mkdirSync('artifacts/.auth', { recursive: true });

    const browser = await chromium.launch();
    const page = await browser.newPage();
    await page.goto('http://127.0.0.1:8001/login');
    await page.locator('input[type="email"]').fill('admin@djezzy.test');
    await page.locator('input[type="password"]').fill('password');
    await page.locator('button[type="submit"]').click();
    await page.waitForURL(/dashboard|admin/);
    await page.context().storageState({ path: AUTH_FILE });
    await browser.close();
}

export default globalSetup;

export { AUTH_FILE };