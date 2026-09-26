import type { InertiaLinkProps } from '@inertiajs/react';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function formatDate(value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (value instanceof Date) {
        return value.toISOString().split('T')[0];
    }

    const str = String(value).trim();

    if (/^\d{4}-\d{2}-\d{2}/.test(str)) {
        return str.slice(0, 10);
    }

    return str;
}

export function formatDateTime(value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (value instanceof Date) {
        return value.toISOString().replace('T', ' ').slice(0, 16);
    }

    const str = String(value).trim();

    if (/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}/.test(str)) {
        return str.replace('T', ' ').slice(0, 16);
    }

    if (/^\d{4}-\d{2}-\d{2}/.test(str)) {
        return str.slice(0, 10);
    }

    return str;
}
