import { Head } from '@inertiajs/react';

type PublicClient = {
    name: string;
    initials: string;
    logoUrl: string | null;
};

type Session = {
    id: number;
    date: string;
    dateLabel: string;
    type: 'photo' | 'video' | 'photo_video';
    typeLabel: string;
};

type Props = {
    client: PublicClient;
    sessions: Session[];
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

export default function PublicShooting({ client, sessions }: Props) {
    const span =
        sessions.length > 0
            ? `${sessions[0].dateLabel} — ${sessions[sessions.length - 1].dateLabel}`
            : null;

    return (
        <div className="mx-auto max-w-(--container-reading) px-4 py-8">
            <Head title={`${client.name} — Sessioni di shooting`} />

            <header className="mb-10 space-y-3 text-center">
                {client.logoUrl ? (
                    <img
                        src={client.logoUrl}
                        alt={client.name}
                        className="mx-auto size-14 rounded-md object-contain"
                    />
                ) : (
                    <div className="mx-auto flex size-14 items-center justify-center rounded-md bg-muted font-serif">
                        {client.initials}
                    </div>
                )}
                <p className="text-sm text-muted-foreground">
                    Sessioni di shooting
                </p>
                <h1 className="font-serif text-3xl">{client.name}</h1>
            </header>

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
                                    <span className="font-mono text-sm text-muted-foreground uppercase">
                                        {s.typeLabel}
                                    </span>
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
