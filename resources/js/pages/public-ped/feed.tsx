import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { ContentDetailPanel } from '@/components/feed/content-detail-panel';
import { ContentGridView } from '@/components/feed/content-grid-view';
import type { FeedContent, FeedViewerActions } from '@/types/content';

type PublicClient = {
    name: string;
    initials: string;
    logoUrl: string | null;
};

type Props = {
    client: PublicClient;
    quarter: { label: string } | null;
    contents: FeedContent[];
    actions: FeedViewerActions;
};

export default function PublicPedFeed({
    client,
    quarter,
    contents,
    actions,
}: Props) {
    const [selectedId, setSelectedId] = useState<number | null>(
        contents[0]?.id ?? null,
    );

    const selected = contents.find((c) => c.id === selectedId) ?? null;

    const token = window.location.pathname.split('/')[2];

    const approve = () => {
        if (!selected) return;
        router.post(`/ped/${token}/contents/${selected.id}/approve`);
    };

    // Il rifiuto richiede sempre un motivo esplicito (§3.7): un prompt
    // rapido va bene finché non arriva un layout dedicato dal capo.
    const reject = () => {
        if (!selected) return;

        const comment = window.prompt(
            'Perché rifiuti questo contenuto? Il commento aiuta il team a capire cosa cambiare.',
        );

        if (!comment) return;

        router.post(`/ped/${token}/contents/${selected.id}/reject`, {
            comment,
        });
    };

    const submitComment = (body: string) => {
        if (!selected) return;
        router.post(`/ped/${token}/contents/${selected.id}/comment`, {
            body,
        });
    };

    return (
        <div className="mx-auto max-w-5xl px-4 py-8">
            <Head title={`${client.name} — Piano editoriale`} />

            <div className="mb-8 flex items-center gap-4">
                {client.logoUrl ? (
                    <img
                        src={client.logoUrl}
                        alt={client.name}
                        className="size-12 rounded-md object-contain"
                    />
                ) : (
                    <div className="flex size-12 items-center justify-center rounded-md bg-muted font-serif">
                        {client.initials}
                    </div>
                )}
                <div>
                    <h1 className="font-serif text-2xl">{client.name}</h1>
                    {quarter && (
                        <p className="text-sm text-muted-foreground">
                            {quarter.label}
                        </p>
                    )}
                </div>
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
                    onSubmitComment={submitComment}
                />
            </div>
        </div>
    );
}
