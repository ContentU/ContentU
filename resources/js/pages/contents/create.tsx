import { Head } from '@inertiajs/react';
import {
    ContentForm,
    type ContentTag,
    type ContentType,
} from '@/pages/contents/form';

type Props = {
    quarter: {
        id: number;
        label: string;
        client: { id: number; name: string };
    };
    contentTypes: ContentType[];
    tags: ContentTag[];
    channels: string[];
};

export default function ContentsCreate({
    quarter,
    contentTypes,
    tags,
    channels,
}: Props) {
    return (
        <>
            <Head title={`Nuovo contenuto — ${quarter.label}`} />

            <div className="p-4 md:p-6">
                <p className="text-sm text-muted-foreground">
                    {quarter.client.name} — {quarter.label}
                </p>
                <h1 className="font-serif text-2xl">Nuovo contenuto</h1>

                <div className="mt-6">
                    <ContentForm
                        quarterId={quarter.id}
                        contentTypes={contentTypes}
                        tags={tags}
                        channels={channels}
                    />
                </div>
            </div>
        </>
    );
}
