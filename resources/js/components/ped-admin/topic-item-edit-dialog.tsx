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

export type EditableTopicItem = {
    id: number;
    formatLabel: string;
    periodLabel: string;
    title: string;
    theme: string;
    objective: string;
    footnote: string | null;
};

/** Modifica un argomento del mese (TopicPreviewItemController@update), stessi campi di pages/topics/index.tsx. */
export function TopicItemEditDialog({ item }: { item: EditableTopicItem }) {
    const [open, setOpen] = useState(false);
    const { data, setData, put, processing, errors, reset } = useForm({
        format_label: item.formatLabel,
        period_label: item.periodLabel,
        title: item.title,
        theme: item.theme,
        objective: item.objective,
        footnote: item.footnote ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/topic-items/${item.id}`, {
            preserveScroll: true,
            onSuccess: () => setOpen(false),
        });
    };

    return (
        <Dialog
            open={open}
            onOpenChange={(v) => {
                setOpen(v);
                if (!v) reset();
            }}
        >
            <DialogTrigger asChild>
                <EditButton label="Modifica argomento" />
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Modifica argomento</DialogTitle>
                </DialogHeader>

                <form
                    id={`topic-item-form-${item.id}`}
                    onSubmit={submit}
                    className="grid gap-3 sm:grid-cols-2"
                >
                    <div className="grid gap-2">
                        <Label>Formato</Label>
                        <Input
                            value={data.format_label}
                            onChange={(e) =>
                                setData('format_label', e.target.value)
                            }
                        />
                        {errors.format_label && (
                            <p className="text-sm text-destructive">
                                {errors.format_label}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <Label>Periodo</Label>
                        <Input
                            value={data.period_label}
                            onChange={(e) =>
                                setData('period_label', e.target.value)
                            }
                        />
                        {errors.period_label && (
                            <p className="text-sm text-destructive">
                                {errors.period_label}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2 sm:col-span-2">
                        <Label>Titolo</Label>
                        <Input
                            value={data.title}
                            onChange={(e) => setData('title', e.target.value)}
                        />
                        {errors.title && (
                            <p className="text-sm text-destructive">
                                {errors.title}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <Label>Tema</Label>
                        <Input
                            value={data.theme}
                            onChange={(e) => setData('theme', e.target.value)}
                        />
                        {errors.theme && (
                            <p className="text-sm text-destructive">
                                {errors.theme}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <Label>Obiettivo</Label>
                        <Input
                            value={data.objective}
                            onChange={(e) =>
                                setData('objective', e.target.value)
                            }
                        />
                        {errors.objective && (
                            <p className="text-sm text-destructive">
                                {errors.objective}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2 sm:col-span-2">
                        <Label>Nota a piè di card (opzionale)</Label>
                        <Textarea
                            value={data.footnote}
                            onChange={(e) =>
                                setData('footnote', e.target.value)
                            }
                        />
                    </div>
                </form>

                <DialogFooter>
                    <Button
                        type="submit"
                        form={`topic-item-form-${item.id}`}
                        disabled={processing}
                    >
                        Salva
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
