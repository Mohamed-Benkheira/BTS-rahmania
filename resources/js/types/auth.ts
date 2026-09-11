export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    roles: string[];
    employee?: Employee | null;
};

import type { FlashToast } from '@/types/ui';

export type Flash = {
    toast?: FlashToast | null;
};

export type Employee = {
    id: number;
    user_id: number | null;
    employee_code: string;
    first_name: string;
    last_name: string;
    full_name: string;
    phone: string | null;
    birth_date: string | null;
    hire_date: string | null;
    employment_type: string;
    employment_status: string;
    biography: string | null;
    profile_photo_path: string | null;
    position?: { id: number; name: string } | null;
    department?: { id: number; name: string } | null;
    team?: { id: number; name: string } | null;
    businessUnit?: { id: number; name: string } | null;
    manager?: { id: number; full_name: string } | null;
    primaryLocation?: { id: number; name: string } | null;
}

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
