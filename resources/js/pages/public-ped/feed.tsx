import { Head, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ContentCalendarView } from '@/components/feed/content-calendar-view';
import { ContentDetailPanel } from '@/components/feed/content-detail-panel';
import { ContentGridView } from '@/components/feed/content-grid-view';
import type { PublicClient } from '@/components/public-ped/client-brand';
import { Button } from '@/components/ui/button';
import type { FeedContent, FeedViewerActions } from '@/types/content';

const LAYOUT_STORAGE_KEY = 'ped-public-feed-layout';

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
    const [layout, setLayout] = useState<'grid' | 'calendar'>('grid');

    useEffect(() => {
        const stored = localStorage.getItem(LAYOUT_STORAGE_KEY);
        if (stored === 'grid' || stored === 'calendar') {
            setLayout(stored);
        }
    }, []);

    const changeLayout = (next: 'grid' | 'calendar') => {
        setLayout(next);
        localStorage.setItem(LAYOUT_STORAGE_KEY, next);
    };

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

            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 className="font-serif text-2xl">Feed contenuti</h1>
                    {quarter && (
                        <p className="text-sm text-muted-foreground">
                            {quarter.label}
                        </p>
                    )}
                </div>
                <div className="flex gap-2">
                    <Button
                        type="button"
                        size="sm"
                        variant={layout === 'grid' ? 'default' : 'outline'}
                        onClick={() => changeLayout('grid')}
                    >
                        Feed
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        variant={layout === 'calendar' ? 'default' : 'outline'}
                        onClick={() => changeLayout('calendar')}
                    >
                        Calendario
                    </Button>
                </div>
            </div>

            <div className="grid gap-6 lg:grid-cols-[2fr_1fr]">
                <div>
                    {layout === 'grid' ? (
                        <ContentGridView
                            contents={contents}
                            selectedId={selectedId}
                            onSelect={setSelectedId}
                        />
                    ) : (
                        <ContentCalendarView
                            contents={contents}
                            selectedId={selectedId}
                            onSelect={setSelectedId}
                        />
                    )}

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
                    editInPlace
                />
            </div>
        </div>
    );
}
