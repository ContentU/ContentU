import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
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
import { Textarea } from '@/components/ui/textarea';

export type ContentType = {
    id: number;
    label: string;
    requiresSecondaryAsset: boolean;
};

export type ContentTag = {
    id: number;
    label: string;
};

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
    tagIds: number[];
};

export type Readiness = {
    blocking: string[];
    warnings: string[];
};

export function ContentForm({
    quarterId,
    contentTypes,
    tags,
    channels,
    content,
    readiness,
}: {
    quarterId: number;
    contentTypes: ContentType[];
    tags: ContentTag[];
    channels: string[];
    content?: EditableContent;
    readiness?: Readiness;
}) {
    const isEdit = !!content;

    const { data, setData, post, transform, processing, errors } = useForm({
        content_type_id: content?.contentTypeId ?? contentTypes[0]?.id ?? '',
        title: content?.title ?? '',
        caption: content?.caption ?? '',
        hashtags: content?.hashtags ?? '',
        resource_url: content?.resourceUrl ?? '',
        cover_resource_url: content?.coverResourceUrl ?? '',
        publish_at: content?.publishAt ?? '',
        channels: content?.channels ?? ([] as string[]),
        tag_ids: content?.tagIds ?? ([] as number[]),
    });

    const selectedType = contentTypes.find(
        (t) => t.id === Number(data.content_type_id),
    );

    const toggleChannel = (channel: string) =>
        setData(
            'channels',
            data.channels.includes(channel)
                ? data.channels.filter((c) => c !== channel)
                : [...data.channels, channel],
        );

    const toggleTag = (id: number) =>
        setData(
            'tag_ids',
            data.tag_ids.includes(id)
                ? data.tag_ids.filter((t) => t !== id)
                : [...data.tag_ids, id],
        );

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        if (isEdit && content) {
            transform((data) => ({ ...data, _method: 'put' }));
            post(`/contents/${content.id}`);
        } else {
            post(`/quarters/${quarterId}/contents`);
        }
    };

    return (
        <form onSubmit={submit} className="max-w-2xl space-y-8">
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="title">Titolo interno</Label>
                    <Input
                        id="title"
                        value={data.title}
                        onChange={(e) => setData('title', e.target.value)}
                        required
                    />
                    {errors.title && (
                        <p className="text-sm text-destructive">
                            {errors.title}
                        </p>
                    )}
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="content_type_id">Tipologia</Label>
                    <Select
                        value={String(data.content_type_id)}
                        onValueChange={(v) =>
                            setData('content_type_id', Number(v))
                        }
                    >
                        <SelectTrigger id="content_type_id">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {contentTypes.map((type) => (
                                <SelectItem
                                    key={type.id}
                                    value={String(type.id)}
                                >
                                    {type.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {errors.content_type_id && (
                        <p className="text-sm text-destructive">
                            {errors.content_type_id}
                        </p>
                    )}
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="publish_at">Data di pubblicazione</Label>
                    <Input
                        id="publish_at"
                        type="datetime-local"
                        value={data.publish_at}
                        onChange={(e) =>
                            setData('publish_at', e.target.value)
                        }
                        required
                    />
                    {errors.publish_at && (
                        <p className="text-sm text-destructive">
                            {errors.publish_at}
                        </p>
                    )}
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="resource_url">Link alla risorsa</Label>
                    <Input
                        id="resource_url"
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
                </div>

                {selectedType?.requiresSecondaryAsset && (
                    <div className="grid gap-2">
                        <Label htmlFor="cover_resource_url">
                            Link alla cover
                        </Label>
                        <Input
                            id="cover_resource_url"
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
                    </div>
                )}
            </div>

            <div className="grid gap-4">
                <div className="grid gap-2">
                    <Label htmlFor="caption">Caption</Label>
                    <Textarea
                        id="caption"
                        value={data.caption}
                        onChange={(e) => setData('caption', e.target.value)}
                    />
                    {errors.caption && (
                        <p className="text-sm text-destructive">
                            {errors.caption}
                        </p>
                    )}
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="hashtags">Hashtag</Label>
                    <Textarea
                        id="hashtags"
                        value={data.hashtags}
                        onChange={(e) => setData('hashtags', e.target.value)}
                    />
                    {errors.hashtags && (
                        <p className="text-sm text-destructive">
                            {errors.hashtags}
                        </p>
                    )}
                </div>
            </div>

            <div className="space-y-2">
                <h2 className="font-medium">Canali</h2>
                <div className="flex flex-wrap gap-4">
                    {channels.map((channel) => (
                        <label
                            key={channel}
                            className="flex items-center gap-2 text-sm capitalize"
                        >
                            <Checkbox
                                checked={data.channels.includes(channel)}
                                onCheckedChange={() => toggleChannel(channel)}
                            />
                            {channel}
                        </label>
                    ))}
                </div>
            </div>

            <div className="space-y-2">
                <h2 className="font-medium">Tag interni</h2>
                <div className="flex flex-wrap gap-4">
                    {tags.map((tag) => (
                        <label
                            key={tag.id}
                            className="flex items-center gap-2 text-sm"
                        >
                            <Checkbox
                                checked={data.tag_ids.includes(tag.id)}
                                onCheckedChange={() => toggleTag(tag.id)}
                            />
                            {tag.label}
                        </label>
                    ))}
                    {tags.length === 0 && (
                        <p className="text-sm text-muted-foreground">
                            Nessun tag disponibile per questo cliente.
                        </p>
                    )}
                </div>
            </div>

            {readiness && (
                <div className="space-y-1 rounded-lg border border-border p-4">
                    <h2 className="font-medium">
                        Checklist pre-pubblicazione
                    </h2>
                    {readiness.blocking.map((message) => (
                        <p
                            key={message}
                            className="text-sm text-destructive"
                        >
                            {message}
                        </p>
                    ))}
                    {readiness.warnings.map((message) => (
                        <p
                            key={message}
                            className="text-sm text-muted-foreground"
                        >
                            {message}
                        </p>
                    ))}
                    {readiness.blocking.length === 0 &&
                        readiness.warnings.length === 0 && (
                            <p className="text-sm text-muted-foreground">
                                Tutto pronto per la programmazione.
                            </p>
                        )}
                </div>
            )}

            <div className="flex items-center gap-4">
                <Button type="submit" disabled={processing}>
                    Salva
                </Button>
            </div>
        </form>
    );
}
