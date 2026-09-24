import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { ContentDetailPanel } from '@/components/feed/content-detail-panel';
import { ContentGridView } from '@/components/feed/content-grid-view';
import type { PublicClient } from '@/components/public-ped/client-brand';
import type { FeedContent, FeedViewerActions } from '@/types/content';

type Props = {
    client: PublicClient;
    clientSlug: string;
    quarter: { label: string } | null;
    contents: FeedContent[];
    actions: FeedViewerActions;
};

export default function PublicPedFeed({
    client,
    clientSlug,
    quarter,
    contents,
    actions,
}: Props) {
    const [selectedId, setSelectedId] = useState<number | null>(
        contents[0]?.id ?? null,
    );

    const selected = contents.find((c) => c.id === selectedId) ?? null;

    const approve = () => {
        if (!selected) return;
        router.post(`/ped/${clientSlug}/contents/${selected.id}/approve`);
    };

    // Il rifiuto richiede sempre un motivo esplicito (§3.7): form inline,
    // gestito da ContentDetailPanel via rejectRequiresComment.
    const reject = (comment?: string) => {
        if (!selected || !comment) return;

        router.post(`/ped/${clientSlug}/contents/${selected.id}/reject`, {
            comment,
        });
    };

    const submitComment = (body: string) => {
        if (!selected) return;
        router.post(`/ped/${clientSlug}/contents/${selected.id}/comment`, {
            body,
        });
    };

    return (
        <div className="mx-auto max-w-5xl">
            <Head title={`${client.name} — Piano editoriale`} />

            <div className="mb-6">
                <h1 className="font-serif text-2xl">Feed contenuti</h1>
                {quarter && (
                    <p className="text-sm text-muted-foreground">
                        {quarter.label}
                    </p>
                )}
            </div>

            <div className="grid gap-6 lg:grid-cols-[2fr_1fr]">
                <div>
                    <ContentGridView
                        contents={contents}
                        selectedId={selectedId}
                        onSelect={setSelectedId}
                    />

                    {contents.length === 0 && (
                        <p className="mt-10 text-center text-sm text-muted-foreground">
                            Nessun contenuto da mostrare al momento.
                        </p>
                    )}
                </div>

                <ContentDetailPanel
                    content={selected}
                    actions={actions}
                    onApprove={approve}
                    onReject={reject}
                    rejectRequiresComment
                    onSubmitComment={submitComment}
                />
            </div>
        </div>
    );
}
