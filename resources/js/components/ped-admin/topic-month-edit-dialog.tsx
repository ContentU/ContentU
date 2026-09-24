import { useForm } from '@inertiajs/react';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

export type EditableTopicMonth = {
    id: number;
    label: string;
    status: 'draft' | 'ready';
    note: string | null;
};

/** Modifica il mese (TopicPreviewController@update): month_label e note. Lo status va sempre reinviato invariato. */
export function TopicMonthEditDialog({ month }: { month: EditableTopicMonth }) {
    const [open, setOpen] = useState(false);
    const { data, setData, put, processing, errors, reset } = useForm({
        month_label: month.label,
        status: month.status,
        note: month.note ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/topics/${month.id}`, {
            preserveScroll: true,
            onSuccess: () => setOpen(false),
        });
    };

    // Campo non mostrato nel form (status è reinviato invariato).
    const knownFields = ['month_label', 'note'];
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
                <EditButton label="Modifica mese" />
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Modifica mese</DialogTitle>
                </DialogHeader>

                <form
                    id={`topic-month-form-${month.id}`}
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
                        <Label>Nome mese</Label>
                        <Input
                            value={data.month_label}
                            onChange={(e) =>
                                setData('month_label', e.target.value)
                            }
                        />
                        {errors.month_label && (
                            <p className="text-sm text-destructive">
                                {errors.month_label}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <Label>Nota del mese</Label>
                        <Textarea
                            value={data.note}
                            onChange={(e) => setData('note', e.target.value)}
                        />
                    </div>
                </form>

                <DialogFooter>
                    <Button
                        type="submit"
                        form={`topic-month-form-${month.id}`}
                        disabled={processing}
                    >
                        Salva
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
