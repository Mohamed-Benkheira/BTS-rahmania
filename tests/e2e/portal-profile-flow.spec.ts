import { execSync } from 'node:child_process';
import { expect, test, type Browser, type Page } from '@playwright/test';

const EMPLOYEE_EMAIL = 'e2e.employee@djezzy.test';
const EMPLOYEE_PASSWORD = 'password';

function ensureEmployee(): void {
    execSync('php artisan app:ensure-e2e-employee', { cwd: process.cwd() });
}

async function loginAsEmployee(browser: Browser): Promise<Page> {
    const context = await browser.newContext({
        storageState: undefined,
    });
    const page = await context.newPage();
    await page.goto('/login');
    await page.locator('input[type="email"]').fill(EMPLOYEE_EMAIL);
    await page.locator('input[type="password"]').fill(EMPLOYEE_PASSWORD);
    await page.locator('button[type="submit"]').click();
    await page.waitForURL(/portal/);
    return page;
}

test.describe('employee portal profile change flow', () => {
    test('submit, HR approve, employee sees approved + unread bell', async ({
        browser,
        page: adminPage,
    }) => {
        ensureEmployee();

        const employeePage = await loginAsEmployee(browser);

        await employeePage.goto('/portal/languages');
        await expect(
            employeePage.getByRole('heading', { name: 'My Languages' }),
        ).toBeVisible();

        await employeePage.locator('#language_id').click();
        await employeePage.getByRole('option', { name: 'English' }).click();
        await employeePage.locator('#speaking_level').click();
        await employeePage.getByRole('option', { name: 'Native' }).click();
        await employeePage
            .getByRole('button', { name: 'Submit for approval' })
            .click();

        await expect(
            employeePage.getByText('Language change submitted for approval.'),
        ).toBeVisible();

        await employeePage.goto('/portal/my-requests');
        await expect(
            employeePage.getByText('language change', { exact: false }).first(),
        ).toBeVisible();
        await expect(
            employeePage.locator('span').filter({ hasText: 'Pending' }).first(),
        ).toBeVisible();

        await adminPage.goto('/admin/profile-change-requests');
        await expect(adminPage.locator('table').first()).toBeVisible();

        const row = adminPage
            .locator('tbody tr')
            .filter({ hasText: 'E2E Employee' })
            .first();
        await expect(row).toBeVisible();
        await row.getByRole('link').first().click();

        await expect(
            adminPage.getByText('Proposed changes').first(),
        ).toBeVisible();

        const approve = adminPage.getByRole('button', { name: 'Approve' }).first();
        await expect(approve).toBeVisible();
        await approve.click();
        const dialog = adminPage.getByRole('dialog');
        await dialog.getByRole('button', { name: 'Approve' }).click();
        await expect(adminPage.getByText('Change request approved')).toBeVisible({
            timeout: 10_000,
        });

        await employeePage.goto('/portal/my-requests');
        await expect(
            employeePage.locator('span').filter({ hasText: 'Approved' }).first(),
        ).toBeVisible({ timeout: 10_000 });

        const bell = employeePage.locator('button[aria-label="Notifications"]');
        await expect(
            bell.locator('span').filter({ hasText: /[1-9]/ }).first(),
        ).toBeVisible({ timeout: 10_000 });
        await bell.click();
        await expect(
            employeePage.getByText('Language change request approved').first(),
        ).toBeVisible({ timeout: 10_000 });

        await employeePage.screenshot({
            fullPage: false,
            path: 'artifacts/portal-approved-request.png',
        });
    });

    test('admin sees pending notification after employee submits', async ({
        browser,
        page: adminPage,
    }) => {
        ensureEmployee();

        const employeePage = await loginAsEmployee(browser);

        await employeePage.goto('/portal/profile');
        await expect(
            employeePage.getByRole('heading', { name: 'My Profile' }),
        ).toBeVisible();

        await employeePage.locator('#phone').fill('+213 555 42 42 42');
        await employeePage
            .getByRole('button', { name: 'Submit for approval' })
            .click();

        await expect(
            employeePage.getByText('Profile change submitted for approval.'),
        ).toBeVisible();

        await adminPage.goto('/admin/profile-change-requests');
        await expect(adminPage.locator('table').first()).toBeVisible();
        const row = adminPage
            .locator('tbody tr')
            .filter({ hasText: 'E2E Employee' })
            .first();
        await expect(row).toBeVisible();
        await expect(
            row.locator('.fi-badge, span').filter({ hasText: 'Pending' }).first(),
        ).toBeVisible();
    });
});