import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
import { ContentDetailPanel } from '@/components/feed/content-detail-panel';
import { ContentGridView } from '@/components/feed/content-grid-view';
import { ContentListView } from '@/components/feed/content-list-view';
import { StatusBadge, type DomainStatus } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import type { FeedContent, FeedViewerActions } from '@/types/content';

type Props = {
    client: { id: number; name: string };
    quarter: {
        id: number;
        label: string;
        status: DomainStatus;
        statusLabel: string;
    };
    contents: FeedContent[];
    actions: FeedViewerActions;
    view: 'all' | 'published';
};

const LAYOUT_STORAGE_KEY = 'ped-feed-layout';

export default function QuarterFeed({
    client,
    quarter,
    contents,
    actions,
    view,
}: Props) {
    const [layout, setLayout] = useState<'grid' | 'list'>('grid');
    const [selectedId, setSelectedId] = useState<number | null>(
        contents[0]?.id ?? null,
    );

    useEffect(() => {
        const stored = localStorage.getItem(LAYOUT_STORAGE_KEY);
        if (stored === 'grid' || stored === 'list') {
            setLayout(stored);
        }
    }, []);

    const changeLayout = (next: 'grid' | 'list') => {
        setLayout(next);
        localStorage.setItem(LAYOUT_STORAGE_KEY, next);
    };

    const selected = contents.find((c) => c.id === selectedId) ?? null;

    const updateStatus = (status: string) => {
        if (!selected) return;
        router.patch(
            `/contents/${selected.id}/status`,
            { status },
            {
                onError: (errors) => {
                    if (errors.status) toast.error(errors.status);
                },
            },
        );
    };

    return (
        <>
            <Head title={`Feed — ${quarter.label}`} />

            <div className="p-4 md:p-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p className="text-sm text-muted-foreground">
                            {client.name}
                        </p>
                        <h1 className="font-serif text-2xl">
                            Feed — {quarter.label}
                        </h1>
                    </div>
                    <StatusBadge
                        status={quarter.status}
                        label={quarter.statusLabel}
                    />
                </div>

                <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <div className="flex gap-2">
                        <Button
                            type="button"
                            size="sm"
                            variant={layout === 'grid' ? 'default' : 'outline'}
                            onClick={() => changeLayout('grid')}
                        >
                            Griglia
                        </Button>
                        <Button
                            type="button"
                            size="sm"
                            variant={layout === 'list' ? 'default' : 'outline'}
                            onClick={() => changeLayout('list')}
                        >
                            Elenco
                        </Button>
                    </div>

                    <div className="flex gap-2">
                        <Button
                            asChild
                            size="sm"
                            variant={view === 'all' ? 'default' : 'outline'}
                        >
                            <Link href={`/quarters/${quarter.id}/feed`}>
                                Intero PED
                            </Link>
                        </Button>
                        <Button
                            asChild
                            size="sm"
                            variant={
                                view === 'published' ? 'default' : 'outline'
                            }
                        >
                            <Link
                                href={`/quarters/${quarter.id}/feed?view=published`}
                            >
                                Solo pubblicati
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="mt-6 grid gap-6 lg:grid-cols-[2fr_1fr]">
                    <div>
                        {layout === 'grid' ? (
                            <ContentGridView
                                contents={contents}
                                selectedId={selectedId}
                                onSelect={setSelectedId}
                            />
                        ) : (
                            <ContentListView
                                contents={contents}
                                selectedId={selectedId}
                                onSelect={setSelectedId}
                            />
                        )}

                        {contents.length === 0 && (
                            <p className="mt-10 text-center text-sm text-muted-foreground">
                                Nessun contenuto da mostrare.
                            </p>
                        )}
                    </div>

                    <ContentDetailPanel
                        content={selected}
                        actions={actions}
                        onSendToReview={() => updateStatus('in_review')}
                        onApprove={() => updateStatus('approved')}
                        onSchedule={() => updateStatus('scheduled')}
                        onReject={() => updateStatus('needs_changes')}
                        onResumeToDraft={() => updateStatus('draft')}
                        onSubmitComment={(body) => {
                            if (!selected) return;
                            router.post(`/contents/${selected.id}/comments`, {
                                body,
                            });
                        }}
                    />
                </div>
            </div>
        </>
    );
}
