import { Head } from '@inertiajs/react';
import { ShootingSessionEditDialog } from '@/components/ped-admin/shooting-session-edit-dialog';
import type { PublicClient } from '@/components/public-ped/client-brand';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';

type Session = {
    id: number;
    date: string;
    dateLabel: string;
    type: 'photo' | 'video' | 'photo_video';
    typeLabel: string;
};

type Actions = {
    canEdit: boolean;
    canApprove: boolean;
    canComment: boolean;
};

type Props = {
    client: PublicClient;
    sessions: Session[];
    actions: Actions;
};

function prevalentType(sessions: Session[]): string | null {
    if (sessions.length === 0) {
        return null;
    }

    const counts = sessions.reduce<Record<string, number>>((acc, s) => {
        acc[s.typeLabel] = (acc[s.typeLabel] ?? 0) + 1;
        return acc;
    }, {});

    return Object.entries(counts).sort((a, b) => b[1] - a[1])[0][0];
}

export default function PublicShooting({ client, sessions, actions }: Props) {
    const span =
        sessions.length > 0
            ? `${sessions[0].dateLabel} — ${sessions[sessions.length - 1].dateLabel}`
            : null;

    return (
        <div className="mx-auto max-w-(--container-reading)">
            <Head title={`${client.name} — Sessioni di shooting`} />

            <header className="mb-10 space-y-1 text-center">
                <p className="text-sm text-muted-foreground">
                    Sessioni di shooting
                </p>
                <h1 className="font-serif text-3xl">{client.name}</h1>
            </header>

            {client.shootingArtifactUrl && (
                <Card className="mx-auto mb-10 max-w-md space-y-2 p-4 text-center">
                    <h2 className="font-medium">Strategia dello shooting</h2>
                    <Button asChild>
                        <a
                            href={client.shootingArtifactUrl}
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Apri presentazione
                        </a>
                    </Button>
                </Card>
            )}

            {sessions.length > 0 ? (
                <>
                    <section className="mb-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div className="rounded-md border border-border p-4 text-center">
                            <p className="font-mono text-xs tracking-wide text-muted-foreground uppercase">
                                Sessioni previste
                            </p>
                            <p className="mt-1 font-serif text-2xl">
                                {sessions.length}
                            </p>
                        </div>
                        <div className="rounded-md border border-border p-4 text-center">
                            <p className="font-mono text-xs tracking-wide text-muted-foreground uppercase">
                                Periodo
                            </p>
                            <p className="mt-1 font-serif text-lg">{span}</p>
                        </div>
                        <div className="rounded-md border border-border p-4 text-center">
                            <p className="font-mono text-xs tracking-wide text-muted-foreground uppercase">
                                Produzione prevalente
                            </p>
                            <p className="mt-1 font-serif text-lg">
                                {prevalentType(sessions)}
                            </p>
                        </div>
                    </section>

                    <section className="space-y-3">
                        <h2 className="font-serif text-2xl">Le date</h2>
                        <ul className="divide-y divide-border">
                            {sessions.map((s) => (
                                <li
                                    key={s.id}
                                    className="flex items-center justify-between gap-4 py-3"
                                >
                                    <span className="font-serif text-lg">
                                        {s.dateLabel}
                                    </span>
                                    <div className="flex items-center gap-2">
                                        <span className="font-mono text-sm text-muted-foreground uppercase">
                                            {s.typeLabel}
                                        </span>
                                        {actions.canEdit && (
                                            <ShootingSessionEditDialog
                                                session={s}
                                            />
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </section>
                </>
            ) : (
                <p className="text-center text-muted-foreground">
                    Nessuna sessione di shooting programmata al momento.
                </p>
            )}

            <p className="mx-auto mt-10 max-w-prose text-center text-sm text-muted-foreground">
                Le date possono spostarsi in base alla disponibilità: ogni
                variazione viene concordata con il team prima della sessione.
            </p>
        </div>
    );
}
