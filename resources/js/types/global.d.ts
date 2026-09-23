import type { Auth } from '@/types/auth';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

export type NotificationItem = {
    id: string;
    title: string;
    message: string;
    url: string | null;
    client: string | null;
    readAt: string | null;
    at: string;
};

export type NotificationsProp = {
    unreadCount: number;
    items: NotificationItem[];
};

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            notifications: NotificationsProp | null;
            [key: string]: unknown;
        };
    }
}
