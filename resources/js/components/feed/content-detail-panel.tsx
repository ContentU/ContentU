import { Link } from '@inertiajs/react';
import { CommentThread } from '@/components/comments/comment-thread';
import { StatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import type { FeedContent, FeedViewerActions } from '@/types/content';

type Props = {
    content: FeedContent | null;
    actions: FeedViewerActions;
    onApprove?: () => void;
    onReject?: () => void;
    onSubmitComment?: (body: string) => void;
    onResumeToDraft?: () => void;
};

export function ContentDetailPanel({
    content,
    actions,
    onApprove,
    onReject,
    onSubmitComment,
    onResumeToDraft,
}: Props) {
    if (!content) {
        return (
            <div className="flex h-full items-center justify-center rounded-lg border border-dashed border-border p-8 text-center text-sm text-muted-foreground">
                Seleziona un contenuto per vederne il dettaglio.
            </div>
        );
    }

    const lastClientComment = [...content.comments]
        .reverse()
        .find((c) => c.authorLabel === 'Cliente');

    return (
        <div className="space-y-4 rounded-lg border border-border p-4">
            <div className="aspect-square overflow-hidden rounded-sm bg-muted">
                {content.resourceUrl && (
                    <img
                        src={content.resourceUrl}
                        alt=""
                        className="size-full object-cover"
                    />
                )}
            </div>

            <p className="font-mono text-xs uppercase tracking-wide text-muted-foreground">
                {content.typeLabel}
            </p>

            {content.caption && (
                <p className="text-sm">&laquo;{content.caption}&raquo;</p>
            )}

            {content.hashtags && (
                <p className="text-sm text-muted-foreground">
                    {content.hashtags}
                </p>
            )}

            {content.tags.length > 0 && (
                <div className="flex flex-wrap gap-2">
                    {content.tags.map((tag) => (
                        <span
                            key={tag.id}
                            className="rounded-full bg-muted px-2 py-0.5 text-xs"
                        >
                            {tag.label}
                        </span>
                    ))}
                </div>
            )}

            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <StatusBadge status={content.status} />
                <span>Programmato per {content.publishAtLabel}</span>
            </div>

            {content.status === 'needs_changes' && lastClientComment && (
                <div className="rounded-md bg-brand-rose-tint p-3 text-sm">
                    <p className="font-medium">Ultima richiesta del cliente</p>
                    <p>{lastClientComment.body}</p>
                </div>
            )}

            <div className="flex flex-wrap gap-2">
                {actions.canApprove && (
                    <Button onClick={onApprove}>✓ Approva</Button>
                )}
                {actions.canReject && (
                    <Button variant="outline" onClick={onReject}>
                        ✕ Rifiuta
                    </Button>
                )}
                {actions.canEdit && content.status === 'needs_changes' && (
                    <Button variant="outline" onClick={onResumeToDraft}>
                        Riprendi in lavorazione
                    </Button>
                )}
                {actions.canEdit && (
                    <Button variant="ghost" asChild>
                        <Link href={`/contents/${content.id}/edit`}>
                            Modifica
                        </Link>
                    </Button>
                )}
            </div>

            {actions.canComment && (
                <CommentThread
                    comments={content.comments}
                    onSubmit={onSubmitComment}
                />
            )}
        </div>
    );
}
