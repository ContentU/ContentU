import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { EditButton } from '@/components/ped-admin/edit-button';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

export type EditableShootingSession = {
    id: number;
    date: string;
    type: 'photo' | 'video' | 'photo_video';
    isTentative?: boolean;
};

const TYPE_LABELS: Record<EditableShootingSession['type'], string> = {
    photo: 'Foto',
    video: 'Video',
    photo_video: 'Foto + Video',
};

/** Modifica/elimina una sessione shooting: rotte admin già esistenti (ShootingController). */
export function ShootingSessionEditDialog({
    session,
}: {
    session: EditableShootingSession;
}) {
    const [open, setOpen] = useState(false);
    const { data, setData, put, processing, errors, reset } = useForm({
        session_date: session.date,
        type: session.type,
        is_tentative: session.isTentative ?? false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/shooting/sessions/${session.id}`, {
            preserveScroll: true,
            onSuccess: () => setOpen(false),
        });
    };

    const destroy = () => {
        if (!confirm('Eliminare questa sessione di shooting?')) return;

        router.delete(`/shooting/sessions/${session.id}`, {
            preserveScroll: true,
            onSuccess: () => setOpen(false),
        });
    };

    // Campo non mostrato nel form (is_tentative è un checkbox senza messaggio dedicato).
    const knownFields = ['session_date', 'type'];
    const otherErrors = Object.entries(errors).filter(
        ([field]) => !knownFields.includes(field),
    );

    return (
        <Dialog
            open={open}
            onOpenChange={(v) => {
                setOpen(v);
                if (!v) reset();
            }}
        >
            <DialogTrigger asChild>
                <EditButton label="Modifica sessione" />
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Modifica sessione</DialogTitle>
                </DialogHeader>

                <form
                    id={`shooting-session-form-${session.id}`}
                    onSubmit={submit}
                    className="grid gap-3"
                >
                    {otherErrors.length > 0 && (
                        <p className="rounded-md bg-destructive/10 p-2 text-sm text-destructive">
                            Salvataggio non riuscito:{' '}
                            {otherErrors
                                .map(([, message]) => message)
                                .join(' ')}
                        </p>
                    )}

                    <div className="grid gap-2">
                        <Label>Data</Label>
                        <Input
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
                        <Label>Tipo</Label>
                        <Select
                            value={data.type}
                            onValueChange={(v) =>
                                setData(
                                    'type',
                                    v as EditableShootingSession['type'],
                                )
                            }
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {Object.entries(TYPE_LABELS).map(
                                    ([value, label]) => (
                                        <SelectItem key={value} value={value}>
                                            {label}
                                        </SelectItem>
                                    ),
                                )}
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
                        Provvisoria
                    </label>
                </form>

                <DialogFooter className="justify-between">
                    <Button
                        type="button"
                        variant="ghost"
                        className="text-destructive"
                        onClick={destroy}
                    >
                        Elimina
                    </Button>
                    <Button
                        type="submit"
                        form={`shooting-session-form-${session.id}`}
                        disabled={processing}
                    >
                        Salva
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
