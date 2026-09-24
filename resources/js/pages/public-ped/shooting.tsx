import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ShootingSessionEditDialog } from '@/components/ped-admin/shooting-session-edit-dialog';
import type { PublicClient } from '@/components/public-ped/client-brand';
import { SessionsCalendarView } from '@/components/shooting/sessions-calendar-view';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const VIEW_STORAGE_KEY = 'ped.shooting.view';

type Session = {
    id: number;
    date: string;
    dateLabel: string;
    type: 'photo' | 'video' | 'photo_video';
    typeLabel: string;
    isTentative: boolean;
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

/** Dettaglio sessione: solo lettura per ora, la Fase 07 aggiunge approvazione e commenti. */
function SessionDetailDialog({
    session,
    actions,
    onOpenChange,
}: {
    session: Session;
    actions: Actions;
    onOpenChange: (open: boolean) => void;
}) {
    return (
        <Dialog open onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{session.dateLabel}</DialogTitle>
                </DialogHeader>

                <div className="space-y-2 text-sm">
                    <p className="text-muted-foreground">
                        {session.typeLabel}
                        {session.isTentative && ' · provvisoria'}
                    </p>
                </div>

                {actions.canEdit && (
                    <div className="pt-2">
                        <ShootingSessionEditDialog session={session} />
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}

export default function PublicShooting({ client, sessions, actions }: Props) {
    const [view, setView] = useState<'list' | 'calendar'>('list');
    const [selected, setSelected] = useState<Session | null>(null);

    useEffect(() => {
        try {
            const stored = localStorage.getItem(VIEW_STORAGE_KEY);
            if (stored === 'list' || stored === 'calendar') {
                setView(stored);
            }
        } catch {
            // localStorage non disponibile (privacy mode, ecc.): resta sulla vista di default.
        }
    }, []);

    const changeView = (next: 'list' | 'calendar') => {
        setView(next);
        try {
            localStorage.setItem(VIEW_STORAGE_KEY, next);
        } catch {
            // niente persistenza, non è bloccante.
        }
    };

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
                        <div className="flex items-center justify-between gap-3">
                            <h2 className="font-serif text-2xl">Le date</h2>
                            <div className="flex gap-2">
                                <Button
                                    type="button"
                                    size="sm"
                                    variant={
                                        view === 'list' ? 'default' : 'outline'
                                    }
                                    onClick={() => changeView('list')}
                                >
                                    Elenco
                                </Button>
                                <Button
                                    type="button"
                                    size="sm"
                                    variant={
                                        view === 'calendar'
                                            ? 'default'
                                            : 'outline'
                                    }
                                    onClick={() => changeView('calendar')}
                                >
                                    Calendario
                                </Button>
                            </div>
                        </div>

                        {view === 'calendar' ? (
                            <Card className="p-4">
                                <SessionsCalendarView
                                    sessions={sessions}
                                    onSelect={setSelected}
                                />
                            </Card>
                        ) : (
                            <ul className="divide-y divide-border">
                                {sessions.map((s) => (
                                    <li
                                        key={s.id}
                                        className="flex items-center justify-between gap-4 py-3"
                                    >
                                        <button
                                            type="button"
                                            onClick={() => setSelected(s)}
                                            className="font-serif text-lg hover:underline"
                                        >
                                            {s.dateLabel}
                                        </button>
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
                        )}
                    </section>
                </>
            ) : (
                <p className="text-center text-muted-foreground">
                    Nessuna sessione di shooting programmata al momento.
                </p>
            )}

            {selected && (
                <SessionDetailDialog
                    session={selected}
                    actions={actions}
                    onOpenChange={(open) => !open && setSelected(null)}
                />
            )}

            <p className="mx-auto mt-10 max-w-prose text-center text-sm text-muted-foreground">
                Le date possono spostarsi in base alla disponibilità: ogni
                variazione viene concordata con il team prima della sessione.
            </p>
        </div>
    );
}
