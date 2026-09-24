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
import { Textarea } from '@/components/ui/textarea';

/**
 * Modifica la "Lettura d'insieme" del trimestre (QuarterController@update).
 * Se valorizzata, sostituisce nel PED il testo generato da TopicSynthesis.
 */
export function TopicsSummaryEditDialog({
    quarterId,
    topicsSummary,
}: {
    quarterId: number;
    topicsSummary: string | null;
}) {
    const [open, setOpen] = useState(false);
    const { data, setData, patch, processing, errors, reset } = useForm({
        topics_summary: topicsSummary ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(`/quarters/${quarterId}`, {
            preserveScroll: true,
            onSuccess: () => setOpen(false),
        });
    };

    const otherErrors = Object.entries(errors).filter(
        ([field]) => field !== 'topics_summary',
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
                <EditButton label="Modifica lettura d'insieme" />
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Lettura d&apos;insieme</DialogTitle>
                </DialogHeader>

                <form
                    id="topics-summary-form"
                    onSubmit={submit}
                    className="grid gap-2"
                >
                    {otherErrors.length > 0 && (
                        <p className="rounded-md bg-destructive/10 p-2 text-sm text-destructive">
                            Salvataggio non riuscito:{' '}
                            {otherErrors
                                .map(([, message]) => message)
                                .join(' ')}
                        </p>
                    )}
                    <Textarea
                        rows={8}
                        placeholder="Lascia vuoto per usare il testo generato automaticamente."
                        value={data.topics_summary}
                        onChange={(e) =>
                            setData('topics_summary', e.target.value)
                        }
                    />
                    {errors.topics_summary && (
                        <p className="text-sm text-destructive">
                            {errors.topics_summary}
                        </p>
                    )}
                </form>

                <DialogFooter>
                    <Button
                        type="submit"
                        form="topics-summary-form"
                        disabled={processing}
                    >
                        Salva
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
