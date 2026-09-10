import { execSync } from 'node:child_process';
import { expect, test, type Page } from '@playwright/test';

function e2eProjectId(): string {
    const out = execSync('php artisan app:ensure-e2e-project', {
        cwd: process.cwd(),
    })
        .toString()
        .trim();
    return out.split('\n').pop() as string;
}

async function firstProjectId(page: Page): Promise<string> {
    await page.goto('/admin/projects');
    await expect(page.locator('table').first()).toBeVisible();
    const link = page.locator('a[href*="/admin/projects/"][href$="/edit"]').first();
    await expect(link).toBeVisible();
    const href = (await link.getAttribute('href')) as string;
    const id = href.match(/admin\/projects\/(\d+)/)?.[1];
    expect(id, 'project id extracted').toBeTruthy();
    return id as string;
}

async function openTab(page: Page, name: string) {
    const tab = page
        .getByRole('tab', { name, exact: true })
        .or(page.getByRole('button', { name, exact: true }))
        .first();
    await expect(tab).toBeVisible();
    await tab.click();
}

test('admin session from storage state reaches admin panel', async ({ page }) => {
    await page.goto('/admin');
    await expect(page.locator('body')).toBeVisible();
    await expect(page.locator('nav').first()).toBeVisible();
});

test('project edit page exposes Assignments manager and Create Assignment', async ({
    page,
}) => {
    const id = e2eProjectId();
    await page.goto(`/admin/projects/${id}/edit`);

    const createAction = page.getByRole('button', {
        name: 'Create Assignment',
    });
    await expect(createAction).toBeVisible();

    await createAction.click();
    await expect(page.getByText('Create Assignment for [')).toBeVisible();
    const dialog = page.getByRole('dialog');
    await dialog.getByRole('button', { name: 'Create Assignment' }).click();
    await expect(page.getByText('Assignment Created')).toBeVisible({
        timeout: 10_000,
    });

    await page.goto(`/admin/projects/${id}/edit`);
    await openTab(page, 'Assignments');
    await expect(
        page.getByRole('columnheader', { name: 'Type' }).first(),
    ).toBeVisible();
    await expect(page.locator('tbody tr').first()).toBeVisible();
});

test.describe('recommendations modal rendering', () => {
    test('light mode', async ({ page }) => {
        const id = await firstProjectId(page);
        await page.goto(`/admin/projects/${id}/edit`);

        await openTab(page, 'Recommendation Runs');
        await page.getByRole('button', { name: 'View Rankings' }).first().click();

        const list = page.locator('.recommendation-list');
        await expect(list).toBeVisible({ timeout: 10_000 });
        await expect(list.locator('> div').first()).toBeVisible();

        await expect(list).toContainText(/match|mandatory requirement/i);

        await page.screenshot({
            fullPage: false,
            path: 'artifacts/rankings-light.png',
        });
    });

    test('dark mode', async ({ page }) => {
        await page.emulateMedia({ colorScheme: 'dark' });
        const id = await firstProjectId(page);
        await page.goto(`/admin/projects/${id}/edit`);

        await openTab(page, 'Recommendation Runs');
        await page.getByRole('button', { name: 'View Rankings' }).first().click();

        const list = page.locator('.recommendation-list');
        await expect(list).toBeVisible({ timeout: 10_000 });
        await expect(list.locator('> div').first()).toBeVisible();

        const html = await page.locator('html').getAttribute('class');
        expect(html ?? '', 'html has dark class').toContain('dark');

        await page.screenshot({
            fullPage: false,
            path: 'artifacts/rankings-dark.png',
        });
    });
});

test('create assignment from top match action present', async ({ page }) => {
    const id = await firstProjectId(page);
    await page.goto(`/admin/projects/${id}/edit`);

    await openTab(page, 'Recommendation Runs');
    await expect(
        page.getByRole('button', { name: 'View Rankings' }).first(),
    ).toBeVisible({ timeout: 10_000 });

    const action = page
        .getByRole('button', { name: 'Create Assignment from Top Match' })
        .first();
    await expect(action).toBeVisible();
});