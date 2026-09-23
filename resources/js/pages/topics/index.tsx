import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type Item = {
    id: number;
    formatLabel: string;
    periodLabel: string;
    title: string;
    theme: string;
    objective: string;
    footnote: string | null;
    contentId: number | null;
};

type Month = {
    id: number;
    monthLabel: string;
    monthOrder: number;
    status: 'draft' | 'ready';
    statusLabel: string;
    note: string | null;
    items: Item[];
};

type Props = {
    quarter: {
        id: number;
        label: string;
        client: { id: number; name: string };
    };
    months: Month[];
};

function ItemForm({ topicId }: { topicId: number }) {
    const { data, setData, post, processing, reset } = useForm({
        format_label: '',
        period_label: '',
        title: '',
        theme: '',
        objective: '',
        footnote: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/topics/${topicId}/items`, { onSuccess: () => reset() });
    };

    return (
        <form
            onSubmit={submit}
            className="grid gap-2 rounded-md border border-dashed border-border p-3 sm:grid-cols-2"
        >
            <Input
                placeholder="Formato (es. Reel)"
                value={data.format_label}
                onChange={(e) => setData('format_label', e.target.value)}
                required
            />
            <Input
                placeholder="Periodo (es. Inizio ottobre)"
                value={data.period_label}
                onChange={(e) => setData('period_label', e.target.value)}
                required
            />
            <Input
                placeholder="Titolo"
                className="sm:col-span-2"
                value={data.title}
                onChange={(e) => setData('title', e.target.value)}
                required
            />
            <Input
                placeholder="Tema"
                value={data.theme}
                onChange={(e) => setData('theme', e.target.value)}
                required
            />
            <Input
                placeholder="Obiettivo"
                value={data.objective}
                onChange={(e) => setData('objective', e.target.value)}
                required
            />
            <Textarea
                placeholder="Nota a piè di card (opzionale)"
                className="sm:col-span-2"
                value={data.footnote}
                onChange={(e) => setData('footnote', e.target.value)}
            />
            <Button
                type="submit"
                disabled={processing}
                className="sm:col-span-2"
            >
                Aggiungi argomento
            </Button>
        </form>
    );
}

function MonthBlock({ month }: { month: Month }) {
    const toggleStatus = () => {
        router.put(`/topics/${month.id}`, {
            month_label: month.monthLabel,
            status: month.status === 'ready' ? 'draft' : 'ready',
            note: month.note,
        });
    };

    const promote = (itemId: number) => {
        router.post(`/topic-items/${itemId}/promote`);
    };

    const removeItem = (itemId: number) => {
        router.delete(`/topic-items/${itemId}`);
    };

    return (
        <Card className="space-y-4 p-4">
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="font-serif text-xl">{month.monthLabel}</h2>
                    <p className="text-sm text-muted-foreground">
                        {month.items.length} argomenti
                    </p>
                </div>
                <Button type="button" variant="outline" onClick={toggleStatus}>
                    {month.statusLabel}
                </Button>
            </div>

            {month.note && (
                <p className="text-sm text-muted-foreground">{month.note}</p>
            )}

            <div className="space-y-2">
                {month.items.map((item, i) => (
                    <div
                        key={item.id}
                        className="rounded-md border border-border p-3"
                    >
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <p className="font-mono text-xs text-muted-foreground uppercase">
                                    {String(i + 1).padStart(2, '0')} ·{' '}
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
                                    <p className="mt-1 text-xs text-muted-foreground italic">
                                        {item.footnote}
                                    </p>
                                )}
                            </div>
                            <div className="flex shrink-0 flex-col items-end gap-1">
                                {item.contentId ? (
                                    <Link
                                        href={`/contents/${item.contentId}/edit`}
                                        className="text-xs text-primary hover:underline"
                                    >
                                        Vedi contenuto
                                    </Link>
                                ) : (
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        onClick={() => promote(item.id)}
                                    >
                                        Trasforma in contenuto
                                    </Button>
                                )}
                                <Button
                                    type="button"
                                    size="sm"
                                    variant="ghost"
                                    className="text-destructive"
                                    onClick={() => removeItem(item.id)}
                                >
                                    Elimina
                                </Button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            <ItemForm topicId={month.id} />
        </Card>
    );
}

export default function TopicsIndex({ quarter, months }: Props) {
    const { data, setData, post, processing, reset } = useForm({
        month_label: '',
        month_order: 1,
    });

    const addMonth = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/quarters/${quarter.id}/topics`, { onSuccess: () => reset() });
    };

    return (
        <AppLayout>
            <Head title={`Argomenti — ${quarter.label}`} />

            <p className="text-sm text-muted-foreground">
                {quarter.client.name}
            </p>
            <h1 className="font-serif text-2xl">
                Pre-verifica argomenti — {quarter.label}
            </h1>

            <div className="mt-6 space-y-6">
                {months.map((month) => (
                    <MonthBlock key={month.id} month={month} />
                ))}

                {months.length === 0 && (
                    <p className="text-sm text-muted-foreground">
                        Nessun mese ancora aggiunto alla pre-verifica.
                    </p>
                )}
            </div>

            <form
                onSubmit={addMonth}
                className="mt-8 flex max-w-sm items-end gap-3"
            >
                <div className="grid gap-2">
                    <Label htmlFor="month_label">Mese</Label>
                    <Input
                        id="month_label"
                        value={data.month_label}
                        onChange={(e) => setData('month_label', e.target.value)}
                        required
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="month_order">Ordine</Label>
                    <Input
                        id="month_order"
                        type="number"
                        min={1}
                        max={12}
                        value={data.month_order}
                        onChange={(e) =>
                            setData('month_order', Number(e.target.value))
                        }
                    />
                </div>
                <Button type="submit" disabled={processing}>
                    Aggiungi mese
                </Button>
            </form>
        </AppLayout>
    );
}
