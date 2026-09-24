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

export type EditableContent = {
    id: number;
    contentTypeId: number;
    title: string;
    caption: string | null;
    hashtags: string | null;
    resourceUrl: string | null;
    coverResourceUrl: string | null;
    publishAt: string;
    channels: string[];
};

/**
 * Modifica un contenuto del feed (ContentController@update). La validazione
 * server richiede content_type_id, title e publish_at anche se qui si
 * modificano solo gli altri campi: li reinviamo invariati.
 */
export function ContentEditDialog({ content }: { content: EditableContent }) {
    const [open, setOpen] = useState(false);
    const { data, setData, put, processing, errors, reset } = useForm({
        content_type_id: content.contentTypeId,
        title: content.title,
        caption: content.caption ?? '',
        hashtags: content.hashtags ?? '',
        resource_url: content.resourceUrl ?? '',
        cover_resource_url: content.coverResourceUrl ?? '',
        publish_at: content.publishAt.slice(0, 16), // formato datetime-local
        channels: content.channels,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/contents/${content.id}`, {
            preserveScroll: true,
            onSuccess: () => setOpen(false),
        });
    };

    // Campi non mostrati nel form (es. content_type_id, channels, tag_ids):
    // se il server li respinge, il salvataggio altrimenti resterebbe silenzioso.
    const knownFields = [
        'title',
        'caption',
        'hashtags',
        'resource_url',
        'cover_resource_url',
        'publish_at',
    ];
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
                <EditButton label="Modifica contenuto" />
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Modifica contenuto</DialogTitle>
                </DialogHeader>

                <form
                    id={`content-edit-form-${content.id}`}
                    onSubmit={submit}
                    className="grid max-h-[70vh] gap-3 overflow-y-auto pr-1"
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
                        <Label>Caption</Label>
                        <Textarea
                            rows={4}
                            value={data.caption}
                            onChange={(e) => setData('caption', e.target.value)}
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label>Hashtag</Label>
                        <Textarea
                            rows={2}
                            value={data.hashtags}
                            onChange={(e) =>
                                setData('hashtags', e.target.value)
                            }
                        />
                    </div>

                    <div className="grid gap-2">
                        <Label>URL foto/video</Label>
                        <Input
                            type="url"
                            value={data.resource_url}
                            onChange={(e) =>
                                setData('resource_url', e.target.value)
                            }
                        />
                        {errors.resource_url && (
                            <p className="text-sm text-destructive">
                                {errors.resource_url}
                            </p>
                        )}
                        {data.resource_url && (
                            <img
                                src={data.resource_url}
                                alt="Anteprima risorsa"
                                className="h-32 w-full rounded-md border border-border object-cover"
                            />
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label>URL cover</Label>
                        <Input
                            type="url"
                            value={data.cover_resource_url}
                            onChange={(e) =>
                                setData('cover_resource_url', e.target.value)
                            }
                        />
                        {errors.cover_resource_url && (
                            <p className="text-sm text-destructive">
                                {errors.cover_resource_url}
                            </p>
                        )}
                        {data.cover_resource_url && (
                            <img
                                src={data.cover_resource_url}
                                alt="Anteprima cover"
                                className="h-32 w-full rounded-md border border-border object-cover"
                            />
                        )}
                    </div>

                    <div className="grid gap-2">
                        <Label>Data di pubblicazione</Label>
                        <Input
                            type="datetime-local"
                            value={data.publish_at}
                            onChange={(e) =>
                                setData('publish_at', e.target.value)
                            }
                        />
                        {errors.publish_at && (
                            <p className="text-sm text-destructive">
                                {errors.publish_at}
                            </p>
                        )}
                    </div>
                </form>

                <DialogFooter>
                    <Button
                        type="submit"
                        form={`content-edit-form-${content.id}`}
                        disabled={processing}
                    >
                        Salva
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
