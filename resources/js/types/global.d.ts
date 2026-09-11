import type { Auth, Employee, Flash } from '@/types/auth';

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth & { roles: string[]; employee?: Employee | null };
            notifications: { unread_count: number };
            flash: Flash;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}
