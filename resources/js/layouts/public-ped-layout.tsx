import { usePage } from '@inertiajs/react';
import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { PedSidebar } from '@/components/ped-sidebar';
import type { PublicClient } from '@/components/public-ped/client-brand';
import { useCurrentUrl } from '@/hooks/use-current-url';

type PageProps = {
    client: PublicClient;
    clientSlug: string;
    auth: { user: { role: string } | null };
};

const SECTION_LABELS: Record<string, string> = {
    argomenti: 'Argomenti',
    feed: 'Feed',
    shooting: 'Shooting',
};

export default function PublicPedLayout({
    children,
}: {
    children: React.ReactNode;
}) {
    const { client, clientSlug, auth } = usePage<PageProps>().props;
    const { currentUrl } = useCurrentUrl();
    const isAdmin = auth.user?.role === 'admin';

    const section = currentUrl.split('/').filter(Boolean).pop() ?? '';
    const sectionLabel = SECTION_LABELS[section] ?? '';

    return (
        <AppShell variant="sidebar">
            <PedSidebar />
            <AppContent variant="sidebar" className="min-w-0 overflow-x-clip">
                <AppSidebarHeader
                    breadcrumbs={[
                        {
                            title: client.name,
                            href: `/ped/${clientSlug}/argomenti`,
                        },
                        ...(sectionLabel
                            ? [{ title: sectionLabel, href: '#' }]
                            : []),
                    ]}
                />

                {isAdmin && (
                    <p className="bg-brand-rose-tint px-4 py-2 text-center text-xs text-foreground md:px-6">
                        Stai visualizzando il PED come admin: le modifiche sono
                        visibili subito al cliente.
                    </p>
                )}

                <div className="p-4 md:p-6">{children}</div>
            </AppContent>
        </AppShell>
    );
}
