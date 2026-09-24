import { Head, router, useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import Heading from '@/components/heading';
import { SessionsCalendarView } from '@/components/shooting/sessions-calendar-view';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';

const SESSIONS_LAYOUT_STORAGE_KEY = 'shooting-sessions-layout';

type Role = 'photo' | 'video' | 'coordination';
type ShootingType = 'photo' | 'video' | 'photo_video';

type Target = {
    id: number;
    clientName: string;
    periodLabel: string;
    weight: string;
    idealSessions: number;
    plannedSessions: number;
    potentialSessions: number;
    statusNote: string | null;
    atRisk: boolean;
};

type Assignment = {
    role: Role;
    roleInitial: string;
    userName: string;
    isAlternative: boolean;
};

type Session = {
    id: number;
    date: string;
    dateLabel: string;
    clientName: string;
    type: ShootingType;
    typeLabel: string;
    checkpointRequired: boolean;
    checkpointNote: string | null;
    assignments: Assignment[];
};

type WorkloadRow = {
    userId: number;
    name: string;
    days: number;
    overTarget: boolean;
    overMax: boolean;
};

type Option = { id: number; name: string };
type QuarterOption = { id: number; clientId: number; label: string };
type Filters = { search: string | null; sort: string | null };

type Props = {
    targets: Target[];
    sessions: Session[];
    workload: WorkloadRow[];
    clients: Option[];
    quarters: QuarterOption[];
    assignableUsers: Option[];
    planningRules: string | null;
    filters: Filters;
};

export default function ShootingIndex({
    targets,
    sessions,
    workload,
    clients,
    quarters,
    assignableUsers,
    planningRules,
    filters,
}: Props) {
    return (
        <>
            <Head title="Shooting" />

            <div className="space-y-10 p-4 md:p-6">
                <Heading
                    variant="small"
                    title="Modulo Shooting"
                    description="Target trimestrali, calendario sessioni e carico di lavoro del team."
                />

                <PlanningRulesCard initialValue={planningRules} />

                <TargetsBlock
                    targets={targets}
                    clients={clients}
                    quarters={quarters}
                />

                <SessionsBlock
                    sessions={sessions}
                    clients={clients}
                    assignableUsers={assignableUsers}
                    filters={filters}
                />

                <WorkloadBlock workload={workload} />
            </div>
        </>
    );
}

function PlanningRulesCard({ initialValue }: { initialValue: string | null }) {
    const [editing, setEditing] = useState(false);
    const { data, setData, put, processing } = useForm({
        value: initialValue ?? '',
    });

    return (
        <Card className="space-y-3 p-4">
            <div className="flex items-center justify-between">
                <h2 className="font-medium">Regole di pianificazione</h2>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={() => setEditing((v) => !v)}
                >
                    {editing ? 'Annulla' : 'Modifica'}
                </Button>
            </div>

            {editing ? (
                <div className="space-y-2">
                    <Textarea
                        value={data.value}
                        onChange={(e) => setData('value', e.target.value)}
                        rows={4}
                    />
                    <Button
                        type="button"
                        size="sm"
                        disabled={processing}
                        onClick={() =>
                            put('/shooting/planning-rules', {
                                onSuccess: () => setEditing(false),
                            })
                        }
                    >
                        Salva
                    </Button>
                </div>
            ) : (
                <p className="text-sm whitespace-pre-line text-muted-foreground">
                    {initialValue || 'Nessuna regola registrata.'}
                </p>
            )}
        </Card>
    );
}

function TargetsBlock({
    targets,
    clients,
    quarters,
}: {
    targets: Target[];
    clients: Option[];
    quarters: QuarterOption[];
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        client_id: '',
        quarter_id: '',
        ideal_sessions: 4,
        planned_sessions: 0,
        potential_sessions: 0,
        weight: 'M',
        status_note: '',
    });

    const availableQuarters = quarters.filter(
        (q) => String(q.clientId) === data.client_id,
    );

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/shooting/targets', { onSuccess: () => reset() });
    };

    return (
        <section className="space-y-3">
            <h2 className="font-medium">
                Scheda clienti: target vs pianificato
            </h2>

            <Card className="divide-y divide-border">
                {targets.length > 0 && (
                    <div className="flex flex-wrap items-center gap-4 px-4 py-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                        <p className="min-w-40 flex-1">Cliente · periodo</p>
                        <p className="font-mono">
                            Target ideale → pianificate + potenziali
                        </p>
                    </div>
                )}

                {targets.map((t) => (
                    <div
                        key={t.id}
                        className="flex flex-wrap items-center gap-4 p-4"
                    >
                        <div className="min-w-40 flex-1">
                            <p className="font-medium">{t.clientName}</p>
                            <p className="text-sm text-muted-foreground">
                                {t.periodLabel} · peso {t.weight}
                            </p>
                        </div>
                        <p
                            className={cn(
                                'font-mono text-sm',
                                t.atRisk && 'text-destructive',
                            )}
                        >
                            {t.idealSessions} → {t.plannedSessions} +{' '}
                            {t.potentialSessions} potenziale
                        </p>
                        {t.statusNote && (
                            <p className="text-sm text-muted-foreground">
                                {t.statusNote}
                            </p>
                        )}
                    </div>
                ))}

                {targets.length === 0 && (
                    <p className="p-4 text-sm text-muted-foreground">
                        Nessun target registrato.
                    </p>
                )}
            </Card>
            {targets.length > 0 && (
                <p className="text-xs text-muted-foreground">
                    <strong>Target ideale</strong> = sessioni che
                    servirebbero nel trimestre. <strong>Pianificate</strong> =
                    già calendarizzate. <strong>Potenziali</strong> = di
                    riserva, non ancora confermate.
                </p>
            )}

            <form onSubmit={submit} className="flex flex-wrap items-end gap-3">
                <div className="grid gap-2">
                    <Label htmlFor="target_client">Cliente</Label>
                    <Select
                        value={data.client_id}
                        onValueChange={(v) => {
                            setData('client_id', v);
                            setData('quarter_id', '');
                        }}
                    >
                        <SelectTrigger id="target_client" className="w-44">
                            <SelectValue placeholder="Cliente" />
                        </SelectTrigger>
                        <SelectContent>
                            {clients.map((c) => (
                                <SelectItem key={c.id} value={String(c.id)}>
                                    {c.name}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="target_quarter">Trimestre</Label>
                    <Select
                        value={data.quarter_id}
                        onValueChange={(v) => setData('quarter_id', v)}
                    >
                        <SelectTrigger id="target_quarter" className="w-36">
                            <SelectValue placeholder="Trimestre" />
                        </SelectTrigger>
                        <SelectContent>
                            {availableQuarters.map((q) => (
                                <SelectItem key={q.id} value={String(q.id)}>
                                    {q.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="ideal">Ideale</Label>
                    <Input
                        id="ideal"
                        type="number"
                        min={0}
                        className="w-20"
                        value={data.ideal_sessions}
                        onChange={(e) =>
                            setData('ideal_sessions', Number(e.target.value))
                        }
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="planned">Pianificate</Label>
                    <Input
                        id="planned"
                        type="number"
                        min={0}
                        className="w-20"
                        value={data.planned_sessions}
                        onChange={(e) =>
                            setData('planned_sessions', Number(e.target.value))
                        }
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="potential">Potenziali</Label>
                    <Input
                        id="potential"
                        type="number"
                        min={0}
                        className="w-20"
                        value={data.potential_sessions}
                        onChange={(e) =>
                            setData(
                                'potential_sessions',
                                Number(e.target.value),
                            )
                        }
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="weight">Peso</Label>
                    <Select
                        value={data.weight}
                        onValueChange={(v) => setData('weight', v)}
                    >
                        <SelectTrigger id="weight" className="w-20">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="S">S</SelectItem>
                            <SelectItem value="M">M</SelectItem>
                            <SelectItem value="L">L</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <Button type="submit" disabled={processing}>
                    Salva target
                </Button>
                {(errors.client_id || errors.quarter_id) && (
                    <p className="w-full text-sm text-destructive">
                        Seleziona cliente e trimestre.
                    </p>
                )}
            </form>
        </section>
    );
}

function SessionsBlock({
    sessions,
    clients,
    assignableUsers,
    filters,
}: {
    sessions: Session[];
    clients: Option[];
    assignableUsers: Option[];
    filters: Filters;
}) {
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState(filters.search ?? '');
    const sort = filters.sort ?? '-session_date';
    const skipNextSearchEffect = useRef(true);
    const [sessionsLayout, setSessionsLayout] = useState<'list' | 'calendar'>(
        'list',
    );

    useEffect(() => {
        const stored = localStorage.getItem(SESSIONS_LAYOUT_STORAGE_KEY);
        if (stored === 'list' || stored === 'calendar') {
            setSessionsLayout(stored);
        }
    }, []);

    const changeSessionsLayout = (next: 'list' | 'calendar') => {
        setSessionsLayout(next);
        localStorage.setItem(SESSIONS_LAYOUT_STORAGE_KEY, next);
    };

    useEffect(() => {
        if (skipNextSearchEffect.current) {
            skipNextSearchEffect.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                '/shooting',
                { 'filter[search]': search || undefined, sort },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 300);

        return () => clearTimeout(timeout);
    }, [search]);

    const changeSort = (nextSort: string) => {
        router.get(
            '/shooting',
            { 'filter[search]': search || undefined, sort: nextSort },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };
    const { data, setData, post, processing, errors, reset } = useForm({
        client_id: '',
        session_date: '',
        type: 'photo' as ShootingType,
        is_tentative: false,
        checkpoint_required: false,
        checkpoint_note: '',
        internal_note: '',
        assignments: [] as {
            user_id: string;
            role: Role;
            is_alternative: boolean;
        }[],
    });

    const addAssignment = () =>
        setData('assignments', [
            ...data.assignments,
            { user_id: '', role: 'photo', is_alternative: false },
        ]);

    const updateAssignment = (
        i: number,
        field: 'user_id' | 'role' | 'is_alternative',
        value: string | boolean,
    ) =>
        setData(
            'assignments',
            data.assignments.map((a, idx) =>
                idx === i ? { ...a, [field]: value } : a,
            ),
        );

    const removeAssignment = (i: number) =>
        setData(
            'assignments',
            data.assignments.filter((_, idx) => idx !== i),
        );

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/shooting/sessions', {
            onSuccess: () => {
                reset();
                setOpen(false);
            },
        });
    };

    return (
        <section className="space-y-3">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <h2 className="font-medium">Calendario sessioni</h2>

                <Dialog open={open} onOpenChange={setOpen}>
                    <DialogTrigger asChild>
                        <Button type="button">Nuova sessione</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Nuova sessione</DialogTitle>
                        </DialogHeader>

                        <form
                            id="new-session-form"
                            onSubmit={submit}
                            className="space-y-4"
                        >
                            <div className="grid gap-2">
                                <Label htmlFor="session_client">Cliente</Label>
                                <Select
                                    value={data.client_id}
                                    onValueChange={(v) =>
                                        setData('client_id', v)
                                    }
                                >
                                    <SelectTrigger id="session_client">
                                        <SelectValue placeholder="Cliente" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {clients.map((c) => (
                                            <SelectItem
                                                key={c.id}
                                                value={String(c.id)}
                                            >
                                                {c.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.client_id && (
                                    <p className="text-sm text-destructive">
                                        {errors.client_id}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="session_date">Data</Label>
                                <Input
                                    id="session_date"
                                    type="date"
                                    value={data.session_date}
                                    onChange={(e) =>
                                        setData('session_date', e.target.value)
                                    }
                                />
                                {errors.session_date && (
                                    <p className="text-sm text-destructive">
                                        {errors.session_date}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="session_type">Tipo</Label>
                                <Select
                                    value={data.type}
                                    onValueChange={(v) =>
                                        setData('type', v as ShootingType)
                                    }
                                >
                                    <SelectTrigger id="session_type">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="photo">
                                            Foto
                                        </SelectItem>
                                        <SelectItem value="video">
                                            Video
                                        </SelectItem>
                                        <SelectItem value="photo_video">
                                            Foto + Video
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <label className="flex items-center gap-2 text-sm">
                                <Checkbox
                                    checked={data.is_tentative}
                                    onCheckedChange={(v) =>
                                        setData('is_tentative', !!v)
                                    }
                                />
                                Alternative ancora da decidere
                            </label>

                            <label className="flex items-center gap-2 text-sm">
                                <Checkbox
                                    checked={data.checkpoint_required}
                                    onCheckedChange={(v) =>
                                        setData('checkpoint_required', !!v)
                                    }
                                />
                                Richiede checkpoint prima di calendarizzare
                            </label>

                            {data.checkpoint_required && (
                                <Textarea
                                    placeholder="Nota checkpoint"
                                    value={data.checkpoint_note}
                                    onChange={(e) =>
                                        setData(
                                            'checkpoint_note',
                                            e.target.value,
                                        )
                                    }
                                />
                            )}

                            <Textarea
                                placeholder="Nota interna (mai visibile al cliente)"
                                value={data.internal_note}
                                onChange={(e) =>
                                    setData('internal_note', e.target.value)
                                }
                            />

                            <div className="space-y-2">
                                <div className="flex items-center justify-between">
                                    <Label>Assegnazioni</Label>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        onClick={addAssignment}
                                    >
                                        + Aggiungi
                                    </Button>
                                </div>

                                {data.assignments.map((a, i) => (
                                    <div
                                        key={i}
                                        className="flex flex-wrap items-center gap-2"
                                    >
                                        <Select
                                            value={a.user_id}
                                            onValueChange={(v) =>
                                                updateAssignment(
                                                    i,
                                                    'user_id',
                                                    v,
                                                )
                                            }
                                        >
                                            <SelectTrigger className="w-40">
                                                <SelectValue placeholder="Persona" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {assignableUsers.map((u) => (
                                                    <SelectItem
                                                        key={u.id}
                                                        value={String(u.id)}
                                                    >
                                                        {u.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <Select
                                            value={a.role}
                                            onValueChange={(v) =>
                                                updateAssignment(i, 'role', v)
                                            }
                                        >
                                            <SelectTrigger className="w-36">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="photo">
                                                    Foto
                                                </SelectItem>
                                                <SelectItem value="video">
                                                    Video
                                                </SelectItem>
                                                <SelectItem value="coordination">
                                                    Coordinamento
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <label className="flex items-center gap-1 text-sm text-muted-foreground">
                                            <Checkbox
                                                checked={a.is_alternative}
                                                onCheckedChange={(v) =>
                                                    updateAssignment(
                                                        i,
                                                        'is_alternative',
                                                        !!v,
                                                    )
                                                }
                                            />
                                            alternativa
                                        </label>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => removeAssignment(i)}
                                        >
                                            Rimuovi
                                        </Button>
                                    </div>
                                ))}
                            </div>
                        </form>

                        <DialogFooter>
                            <Button
                                type="submit"
                                form="new-session-form"
                                disabled={processing}
                            >
                                Salva sessione
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <div className="flex flex-wrap items-center gap-3">
                <Input
                    type="search"
                    placeholder="Cerca per cliente…"
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="w-56"
                />
                <Select value={sort} onValueChange={changeSort}>
                    <SelectTrigger className="w-56">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="-session_date">
                            Data sessione (più recenti prima)
                        </SelectItem>
                        <SelectItem value="session_date">
                            Data sessione (meno recenti prima)
                        </SelectItem>
                        <SelectItem value="-created_at">
                            Data di inserimento (più recenti prima)
                        </SelectItem>
                        <SelectItem value="created_at">
                            Data di inserimento (meno recenti prima)
                        </SelectItem>
                    </SelectContent>
                </Select>

                <div className="flex gap-2">
                    <Button
                        type="button"
                        size="sm"
                        variant={
                            sessionsLayout === 'list' ? 'default' : 'outline'
                        }
                        onClick={() => changeSessionsLayout('list')}
                    >
                        Elenco
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        variant={
                            sessionsLayout === 'calendar'
                                ? 'default'
                                : 'outline'
                        }
                        onClick={() => changeSessionsLayout('calendar')}
                    >
                        Calendario
                    </Button>
                </div>
            </div>

            {sessionsLayout === 'calendar' && (
                <Card className="p-4">
                    <SessionsCalendarView sessions={sessions} />
                </Card>
            )}

            {sessionsLayout === 'list' && (
                <Card className="divide-y divide-border">
                    {sessions.length > 0 && (
                        <div className="flex flex-wrap items-center justify-between gap-2 px-4 py-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                            <span>Data · cliente · tipo</span>
                            <span>Assegnazioni (F/V/C)</span>
                        </div>
                    )}

                    {sessions.map((s) => (
                        <div key={s.id} className="space-y-2 p-4">
                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <p className="font-medium">
                                    {s.dateLabel} · {s.clientName} · {s.typeLabel}
                                </p>
                                {s.checkpointRequired && (
                                    <Badge
                                        variant="outline"
                                        className="bg-status-review-bg text-status-review"
                                    >
                                        Checkpoint richiesto
                                    </Badge>
                                )}
                            </div>

                            {s.checkpointRequired && s.checkpointNote && (
                                <p className="text-sm text-muted-foreground">
                                    {s.checkpointNote}
                                </p>
                            )}

                            {s.assignments.length > 0 ? (
                                <div className="flex flex-wrap gap-3 text-sm">
                                    {s.assignments
                                        .filter((a) => !a.isAlternative)
                                        .map((a, i) => {
                                            const alt = s.assignments.find(
                                                (x) =>
                                                    x.isAlternative &&
                                                    x.role === a.role,
                                            );

                                            return (
                                                <span key={i}>
                                                    {a.roleInitial}: {a.userName}
                                                    {alt && (
                                                        <span className="text-muted-foreground">
                                                            {' '}
                                                            · (alt. {alt.userName})
                                                        </span>
                                                    )}
                                                </span>
                                            );
                                        })}
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    Nessuna assegnazione.
                                </p>
                            )}
                        </div>
                    ))}

                    {sessions.length === 0 && (
                        <p className="p-4 text-sm text-muted-foreground">
                            Nessuna sessione pianificata.
                        </p>
                    )}
                </Card>
            )}
        </section>
    );
}

function WorkloadBlock({ workload }: { workload: WorkloadRow[] }) {
    const max = Math.max(1, ...workload.map((w) => w.days));

    return (
        <section className="space-y-3">
            <h2 className="font-medium">Cruscotto carico di lavoro</h2>

            <Card className="divide-y divide-border">
                {workload.map((w) => (
                    <div key={w.userId} className="flex items-center gap-4 p-4">
                        <p className="w-40 shrink-0 truncate">{w.name}</p>
                        <div className="h-2 flex-1 overflow-hidden rounded-full bg-muted">
                            <div
                                className={cn(
                                    'h-full rounded-full',
                                    w.overMax
                                        ? 'bg-destructive'
                                        : w.overTarget
                                          ? 'bg-primary/60'
                                          : 'bg-primary',
                                )}
                                style={{
                                    width: `${(w.days / max) * 100}%`,
                                }}
                            />
                        </div>
                        <p className="w-24 shrink-0 text-right text-sm text-muted-foreground">
                            {w.days} giornate
                        </p>
                    </div>
                ))}

                {workload.length === 0 && (
                    <p className="p-4 text-sm text-muted-foreground">
                        Nessun dato per questo mese.
                    </p>
                )}
            </Card>
        </section>
    );
}
