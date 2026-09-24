import { Link, usePage } from '@inertiajs/react';
import { Camera, LayoutDashboard, LayoutGrid, ListChecks } from 'lucide-react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    ClientBrandMark,
    type PublicClient,
} from '@/components/public-ped/client-brand';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

type PageProps = {
    client: PublicClient;
    clientSlug: string;
    auth: { user: { role: string } | null };
};

const adminNavItems: NavItem[] = [
    {
        title: 'Torna alla dashboard',
        href: dashboard(),
        icon: LayoutDashboard,
    },
];

/** Sidebar del PED pubblico: stesso layout della dashboard interna (app-sidebar.tsx), contenuto diverso. */
export function PedSidebar() {
    const { auth, client, clientSlug } = usePage<PageProps>().props;
    const isAdmin = auth.user?.role === 'admin';

    const items: NavItem[] = [
        {
            title: 'Argomenti',
            href: `/ped/${clientSlug}/argomenti`,
            icon: ListChecks,
        },
        {
            title: 'Feed',
            href: `/ped/${clientSlug}/feed`,
            icon: LayoutGrid,
        },
        {
            title: 'Shooting',
            href: `/ped/${clientSlug}/shooting`,
            icon: Camera,
        },
    ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link
                                href={`/ped/${clientSlug}/argomenti`}
                                prefetch
                            >
                                <ClientBrandMark client={client} />
                                <span className="font-serif">
                                    {client.name}
                                </span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={items} label="PED" />
                {isAdmin && <NavMain items={adminNavItems} label="Admin" />}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
