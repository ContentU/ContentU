import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';

type PublicClient = {
    name: string;
    initials: string;
    logoUrl: string | null;
};

type Item = {
    index: number;
    formatLabel: string;
    periodLabel: string;
    title: string;
    theme: string;
    objective: string;
    footnote: string | null;
};

type Month = {
    id: number;
    label: string;
    count: number;
    status: 'draft' | 'ready';
    statusLabel: string;
    note: string | null;
    approvalStatus: string | null;
    items: Item[];
};

type Synthesis = {
    recurringThemes: { theme: string; count: number }[];
    materialToProduce: { month: string; title: string }[];
};

type Props = {
    client: PublicClient;
    quarter: { label: string } | null;
    months: Month[];
    synthesis: Synthesis | null;
};

function MonthBadge({ month }: { month: Month }) {
    return (
        <Badge
            variant="outline"
            className={cn(
                month.status === 'ready'
                    ? 'bg-status-approved-bg text-status-approved'
                    : 'border-dashed bg-status-review-bg text-status-review',
            )}
        >
            {month.statusLabel}
        </Badge>
    );
}

function ApprovalPanel({ month, token }: { month: Month; token: string }) {
    const [comment, setComment] = useState('');

    const respond = (status: 'approved' | 'approved_with_notes' | 'revise') => {
        if (status !== 'approved' && !comment.trim()) {
            window.alert('Aggiungi un commento per motivare la richiesta.');
            return;
        }

        router.post(`/ped/${token}/topics/${month.id}/respond`, {
            status,
            comment: comment.trim() || null,
        });
    };

    return (
        <div className="space-y-3 border-t border-border pt-4">
            <p className="text-sm font-medium">
                Cosa ne pensi degli argomenti di {month.label}?
            </p>
            <Textarea
                placeholder="Commento (obbligatorio se non approvi in pieno)"
                value={comment}
                onChange={(e) => setComment(e.target.value)}
            />
            <div className="flex flex-wrap gap-2">
                <Button onClick={() => respond('approved')}>✓ Approvato</Button>
                <Button
                    variant="outline"
                    onClick={() => respond('approved_with_notes')}
                >
                    ◐ Approvato con modifiche
                </Button>
                <Button
                    variant="outline"
                    className="text-destructive"
                    onClick={() => respond('revise')}
                >
                    ✗ Da rivedere
                </Button>
            </div>
            {month.approvalStatus && (
                <p className="text-xs text-muted-foreground">
                    Ultima risposta registrata: {month.approvalStatus}
                </p>
            )}
        </div>
    );
}

export default function PublicPedTopicPreview({
    client,
    quarter,
    months,
    synthesis,
}: Props) {
    const token = window.location.pathname.split('/')[2];

    return (
        <div className="mx-auto max-w-(--container-reading) px-4 py-8">
            <Head title={`${client.name} — Argomenti del piano editoriale`} />

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
                    Piano editoriale
                </p>
                <h1 className="font-serif text-3xl">{client.name}</h1>
                {quarter && (
                    <p className="text-sm text-muted-foreground">
                        {quarter.label}
                    </p>
                )}
                <p className="mx-auto max-w-prose text-sm text-muted-foreground">
                    Ecco una panoramica degli argomenti che vogliamo trattare in
                    questo trimestre: una validazione di massima, prima di
                    entrare nel dettaglio dei singoli contenuti.
                </p>
            </header>

            {months.length > 0 && (
                <nav className="mb-10 flex flex-wrap justify-center gap-2">
                    {months.map((month) => (
                        <a
                            key={month.id}
                            href={`#mese-${month.id}`}
                            className="rounded-full border border-border px-3 py-1 text-sm hover:bg-muted"
                        >
                            {month.label} ({month.count})
                        </a>
                    ))}
                </nav>
            )}

            <div className="space-y-10">
                {months.map((month) => (
                    <section
                        key={month.id}
                        id={`mese-${month.id}`}
                        className="space-y-4"
                    >
                        <div className="flex items-center justify-between gap-3">
                            <div>
                                <h2 className="font-serif text-2xl">
                                    {month.label}
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    {month.count} argomenti
                                </p>
                            </div>
                            <MonthBadge month={month} />
                        </div>

                        {month.note && (
                            <p className="text-sm text-muted-foreground">
                                {month.note}
                            </p>
                        )}

                        <div className="grid gap-4 sm:grid-cols-2">
                            {month.items.map((item) => (
                                <Card
                                    key={item.index}
                                    className="space-y-2 p-4"
                                >
                                    <p className="font-mono text-xs tracking-wide text-muted-foreground uppercase">
                                        {String(item.index).padStart(2, '0')} ·{' '}
                                        {item.formatLabel} · {item.periodLabel}
                                    </p>
                                    <p className="font-medium">{item.title}</p>
                                    <p className="text-sm text-brand-rose">
                                        {item.theme}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {item.objective}
                                    </p>
                                    {item.footnote && (
                                        <p className="text-xs text-muted-foreground italic">
                                            {item.footnote}
                                        </p>
                                    )}
                                </Card>
                            ))}
                        </div>

                        <ApprovalPanel month={month} token={token} />
                    </section>
                ))}

                {months.length === 0 && (
                    <p className="text-center text-sm text-muted-foreground">
                        Nessun argomento ancora proposto per questo trimestre.
                    </p>
                )}
            </div>

            {synthesis && (
                <section className="mt-14 space-y-4 border-t border-border pt-8">
                    <h2 className="font-serif text-2xl">
                        Lettura d&apos;insieme
                    </h2>

                    {synthesis.recurringThemes.length > 0 && (
                        <div className="flex flex-wrap gap-2">
                            {synthesis.recurringThemes.map((t) => (
                                <Badge key={t.theme} variant="outline">
                                    {t.theme} · {t.count}
                                </Badge>
                            ))}
                        </div>
                    )}

                    <p className="text-sm text-muted-foreground">
                        I temi sopra ricorrono più volte nel trimestre: sono i
                        filoni portanti attorno a cui ruota il piano editoriale.
                    </p>

                    <Card className="space-y-2 p-4">
                        <h3 className="font-medium">Materiale da produrre</h3>
                        {synthesis.materialToProduce.length > 0 ? (
                            <ul className="list-inside list-disc text-sm text-muted-foreground">
                                {synthesis.materialToProduce.map((m, i) => (
                                    <li key={i}>
                                        {m.month}: {m.title}
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                Nessun materiale video da girare al momento.
                            </p>
                        )}
                    </Card>

                    <Card className="space-y-2 bg-brand-rose-tint p-4">
                        <h3 className="font-medium">Cosa ci serve da voi</h3>
                        <p className="text-sm text-muted-foreground">
                            Una validazione di massima su questi temi: se
                            qualcosa non convince, segnalacelo nel commento di
                            ogni mese. Il materiale elencato sopra va girato con
                            il vostro team quando indicato.
                        </p>
                    </Card>
                </section>
            )}

            <p className="mt-12 text-center text-sm text-muted-foreground">
                Una volta approvati i temi, nella sezione Contenuti trovi il
                piano nel dettaglio.{' '}
                <Link
                    href={`/ped/${token}/feed`}
                    className="text-primary hover:underline"
                >
                    Vai ai contenuti
                </Link>
            </p>
        </div>
    );
}
