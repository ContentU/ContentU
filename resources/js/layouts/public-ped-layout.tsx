import { Link, usePage } from '@inertiajs/react';
import { NotificationBell } from '@/components/notification-bell';
import {
    ClientBrandMark,
    type PublicClient,
} from '@/components/public-ped/client-brand';

type PageProps = {
    client: PublicClient;
    clientSlug: string;
    auth: { user: { role: string } | null };
};

export default function PublicPedLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { client, clientSlug, auth } = usePage<PageProps>().props;
    const isAdmin = auth.user?.role === 'admin';

    const tabs = [
        { href: `/ped/${clientSlug}/argomenti`, label: 'Argomenti' },
        { href: `/ped/${clientSlug}/feed`, label: 'Feed' },
        { href: `/ped/${clientSlug}/shooting`, label: 'Shooting' },
    ];

    return (
        <div className="min-h-screen bg-background">
            {isAdmin && (
                <p className="bg-brand-rose-tint px-4 py-2 text-center text-xs text-foreground md:px-6">
                    Stai visualizzando il PED come admin: le modifiche sono
                    visibili subito al cliente.
                </p>
            )}
            <header className="flex h-16 items-center justify-between gap-4 border-b px-4 md:px-6">
                <div className="flex items-center gap-3">
                    <ClientBrandMark client={client} />
                    <span className="font-serif text-lg">{client.name}</span>
                </div>
                <nav className="hidden gap-1 md:flex">
                    {tabs.map((tab) => (
                        <Link
                            key={tab.href}
                            href={tab.href}
                            className="rounded-md px-3 py-2 text-sm text-muted-foreground hover:bg-muted hover:text-foreground"
                        >
                            {tab.label}
                        </Link>
                    ))}
                </nav>
                <NotificationBell />
            </header>
            <main className="p-4 md:p-6">{children}</main>
        </div>
    );
}
