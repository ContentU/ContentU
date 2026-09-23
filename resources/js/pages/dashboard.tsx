import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Avatar, AvatarFallback, AvatarGroup } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';

type ClientRow = {
    id: number;
    name: string;
    initials: string;
    status: 'active' | 'paused' | 'archived';
    pausedAt: string | null;
    health: number | null;
    alerts: string[];
    team: { id: number; name: string; initials: string }[];
};

type Props = {
    stats: {
        activeClients: number;
        openAlerts: number;
        teamSize: number;
        currentQuarter: string;
    };
    clients: ClientRow[];
    archivedCount: number;
    filters: { status: string };
};

const FILTERS = [
    { value: '', label: 'Tutti' },
    { value: 'active', label: 'Attivi' },
    { value: 'paused', label: 'In pausa' },
    { value: 'archived', label: 'Archiviati' },
] as const;

export default function Dashboard({
    stats,
    clients,
    archivedCount,
    filters,
}: Props) {
    const { auth } = usePage().props;

    return (
        <AppLayout>
            <Head title="Dashboard" />

            <div className="flex items-center justify-between gap-4">
                <h1 className="font-serif text-2xl">Dashboard</h1>
                {auth.can.manageClients && (
                    <Button asChild>
                        <Link href="/clients/create">+ Nuovo cliente</Link>
                    </Button>
                )}
            </div>

            <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card className="p-4">
                    <p className="text-sm text-muted-foreground">
                        Clienti attivi
                    </p>
                    <p className="mt-1 text-2xl font-semibold">
                        {stats.activeClients}
                    </p>
                </Card>
                <Card className="p-4">
                    <p className="text-sm text-muted-foreground">
                        Alert da rivedere
                    </p>
                    <p className="mt-1 text-2xl font-semibold">
                        {stats.openAlerts}
                    </p>
                </Card>
                <Card className="p-4">
                    <p className="text-sm text-muted-foreground">
                        Persone in team
                    </p>
                    <p className="mt-1 text-2xl font-semibold">
                        {stats.teamSize}
                    </p>
                </Card>
                <Card className="p-4">
                    <p className="text-sm text-muted-foreground">
                        Trimestre corrente
                    </p>
                    <p className="mt-1 text-2xl font-semibold">
                        {stats.currentQuarter}
                    </p>
                </Card>
            </div>

            <nav className="mt-6 flex gap-4">
                {FILTERS.map((f) => (
                    <Link
                        key={f.value}
                        href={f.value ? `/dashboard?status=${f.value}` : '/dashboard'}
                        preserveScroll
                        className={
                            filters.status === f.value
                                ? 'font-semibold text-primary'
                                : 'text-muted-foreground'
                        }
                    >
                        {f.label}
                    </Link>
                ))}
            </nav>

            <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {clients.map((client) => (
                    <Card key={client.id} className="p-4">
                        <div className="flex items-start justify-between gap-3">
                            <div className="flex items-start gap-3">
                                <span className="flex size-10 items-center justify-center rounded-md bg-accent font-mono text-sm text-accent-foreground">
                                    {client.initials}
                                </span>
                                <div className="min-w-0">
                                    <Link
                                        href={`/clients/${client.id}/edit`}
                                        className="font-medium hover:underline"
                                    >
                                        {client.name}
                                    </Link>
                                    <p className="text-sm text-muted-foreground">
                                        {client.status === 'paused' &&
                                        client.pausedAt
                                            ? `In pausa dal ${client.pausedAt}`
                                            : client.status === 'active'
                                              ? 'Attivo'
                                              : 'Archiviato'}
                                    </p>
                                </div>
                            </div>
                            {client.health !== null && (
                                <Badge
                                    variant="outline"
                                    className="bg-status-approved-bg text-status-approved"
                                >
                                    {client.health}%
                                </Badge>
                            )}
                        </div>

                        {client.health !== null && (
                            <div className="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                <div
                                    className="h-full rounded-full bg-primary"
                                    style={{ width: `${client.health}%` }}
                                />
                            </div>
                        )}

                        {client.alerts.map((message, i) => (
                            <p
                                key={i}
                                className="mt-2 text-sm text-status-review"
                            >
                                ⚠ {message}
                            </p>
                        ))}

                        <div className="mt-3 flex items-center gap-2">
                            <AvatarGroup>
                                {client.team.map((member) => (
                                    <Avatar key={member.id} size="sm">
                                        <AvatarFallback>
                                            {member.initials}
                                        </AvatarFallback>
                                    </Avatar>
                                ))}
                            </AvatarGroup>
                            <span className="text-sm text-muted-foreground">
                                {client.team.length} in team
                            </span>
                        </div>
                    </Card>
                ))}
            </div>

            {clients.length === 0 && (
                <p className="mt-10 text-center text-muted-foreground">
                    Nessun cliente con questo filtro.
                </p>
            )}

            {archivedCount > 0 && !filters.status && (
                <Link
                    href="/dashboard?status=archived"
                    className="mt-6 block text-center text-sm text-muted-foreground hover:underline"
                >
                    + {archivedCount} client
                    {archivedCount === 1 ? 'e' : 'i'} archiviat
                    {archivedCount === 1 ? 'o' : 'i'} — nascosti di default
                </Link>
            )}
        </AppLayout>
    );
}
