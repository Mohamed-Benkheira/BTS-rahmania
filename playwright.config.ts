import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    reporter: [['list']],
    timeout: 60_000,
    globalSetup: './tests/e2e/global-setup.ts',
    workers: 1,
    use: {
        baseURL: 'http://127.0.0.1:8001',
        trace: 'retain-on-failure',
        storageState: 'artifacts/.auth/user.json',
    },
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8001',
        url: 'http://127.0.0.1:8001/login',
        reuseExistingServer: true,
        timeout: 60_000,
    },
});