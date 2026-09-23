import { Head, Link, usePage } from '@inertiajs/react';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

type ClientRow = {
    id: number;
    name: string;
    initials: string;
    status: 'active' | 'paused' | 'archived';
    pausedAt: string | null;
    team: { id: number; name: string }[];
};

type Props = {
    clients: ClientRow[];
    filters: { status: string };
    statuses: { value: string; label: string }[];
};

// Solo token del design system: mai colori letterali (regola DS4).
const STATUS_STYLE = {
    active: 'bg-status-approved-bg text-status-approved',
    paused: 'bg-status-draft-bg text-status-draft',
    archived: 'bg-status-published-bg text-status-published',
} as const;

export default function ClientsIndex({ clients, filters, statuses }: Props) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Clienti" />

            <div className="p-4 md:p-6">
                <div className="flex items-center justify-between gap-4">
                    <h1 className="font-serif text-2xl">Clienti</h1>
                    {auth.can.manageClients && (
                        <Button asChild>
                            <Link href="/clients/create">+ Nuovo cliente</Link>
                        </Button>
                    )}
                </div>

                <nav className="mt-6 flex gap-2">
                    <Link
                        href="/clients"
                        className={
                            !filters.status
                                ? 'font-semibold text-primary'
                                : 'text-muted-foreground'
                        }
                    >
                        Tutti
                    </Link>
                    {statuses.map((s) => (
                        <Link
                            key={s.value}
                            href={`/clients?status=${s.value}`}
                            preserveScroll
                            className={
                                filters.status === s.value
                                    ? 'font-semibold text-primary'
                                    : 'text-muted-foreground'
                            }
                        >
                            {s.label}
                        </Link>
                    ))}
                </nav>

                <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {clients.map((client) => (
                        <Card key={client.id} className="p-4">
                            <div className="flex items-start gap-3">
                                <span className="flex size-10 items-center justify-center rounded-md bg-accent font-mono text-sm text-accent-foreground">
                                    {client.initials}
                                </span>
                                <div className="min-w-0 flex-1">
                                    <Link
                                        href={`/clients/${client.id}/edit`}
                                        className="font-medium hover:underline"
                                    >
                                        {client.name}
                                    </Link>
                                    <div className="mt-1">
                                        <Badge
                                            variant="outline"
                                            className={
                                                STATUS_STYLE[client.status]
                                            }
                                        >
                                            {client.status === 'paused' &&
                                            client.pausedAt
                                                ? `In pausa dal ${client.pausedAt}`
                                                : statuses.find(
                                                      (s) =>
                                                          s.value ===
                                                          client.status,
                                                  )?.label}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                            <p className="mt-3 text-sm text-muted-foreground">
                                {client.team.length} in team
                            </p>
                        </Card>
                    ))}
                </div>

                {clients.length === 0 && (
                    <p className="mt-10 text-center text-muted-foreground">
                        Nessun cliente con questo filtro.
                    </p>
                )}
            </div>
        </>
    );
}
