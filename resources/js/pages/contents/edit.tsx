import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import {
    ContentForm,
    type ContentTag,
    type ContentType,
    type EditableContent,
    type Readiness,
} from '@/pages/contents/form';

type Props = {
    quarter: { id: number; label: string; client: { id: number; name: string } };
    content: EditableContent;
    contentTypes: ContentType[];
    tags: ContentTag[];
    channels: string[];
    readiness: Readiness;
};

export default function ContentsEdit({
    quarter,
    content,
    contentTypes,
    tags,
    channels,
    readiness,
}: Props) {
    return (
        <AppLayout>
            <Head title={`${content.title} — ${quarter.label}`} />

            <p className="text-sm text-muted-foreground">
                {quarter.client.name} — {quarter.label}
            </p>
            <h1 className="font-serif text-2xl">{content.title}</h1>

            <div className="mt-6">
                <ContentForm
                    quarterId={quarter.id}
                    contentTypes={contentTypes}
                    tags={tags}
                    channels={channels}
                    content={content}
                    readiness={readiness}
                />
            </div>
        </AppLayout>
    );
}
