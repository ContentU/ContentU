export type UserRole = 'admin' | 'account_manager' | 'copywriter' | 'client';

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    role: UserRole;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
    can: {
        manageClients: boolean;
        manageUsers: boolean;
        approve: boolean;
        publish: boolean;
        manageShooting: boolean;
    };
};

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
