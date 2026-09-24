import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { CommentThread } from '@/components/comments/comment-thread';
import { ContentEditDialog } from '@/components/ped-admin/content-edit-dialog';
import { StatusBadge } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import type { FeedContent, FeedViewerActions } from '@/types/content';

type Props = {
    content: FeedContent | null;
    actions: FeedViewerActions;
    onApprove?: () => void;
    onSchedule?: () => void;
    onReject?: (comment?: string) => void;
    /** Il portale cliente richiede sempre un motivo esplicito (§3.7): mostra un form inline invece di rifiutare subito. */
    rejectRequiresComment?: boolean;
    onSubmitComment?: (body: string) => void;
    onResumeToDraft?: () => void;
    onSendToReview?: () => void;
    /** Nel PED pubblico l'admin modifica sul posto (dialog); nell'area interna resta il link a /contents/{id}/edit. */
    editInPlace?: boolean;
};

export function ContentDetailPanel({
    content,
    actions,
    onApprove,
    onSchedule,
    onReject,
    rejectRequiresComment = false,
    onSubmitComment,
    onResumeToDraft,
    onSendToReview,
    editInPlace = false,
}: Props) {
    const [rejecting, setRejecting] = useState(false);
    const [rejectComment, setRejectComment] = useState('');

    if (!content) {
        return (
            <div className="flex h-full items-center justify-center rounded-lg border border-dashed border-border p-8 text-center text-sm text-muted-foreground">
                Seleziona un contenuto per vederne il dettaglio.
            </div>
        );
    }

    const startReject = () => {
        if (rejectRequiresComment) {
            setRejecting(true);
            return;
        }

        onReject?.();
    };

    const confirmReject = () => {
        if (!rejectComment.trim()) return;

        onReject?.(rejectComment.trim());
        setRejecting(false);
        setRejectComment('');
    };

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

            <p className="font-mono text-xs tracking-wide text-muted-foreground uppercase">
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
                {actions.canEdit && content.status === 'draft' && (
                    <Button onClick={onSendToReview}>Porta in revisione</Button>
                )}
                {actions.canApprove && content.status === 'in_review' && (
                    <Button onClick={onApprove}>✓ Approva</Button>
                )}
                {onSchedule && content.status === 'approved' && (
                    <Button onClick={onSchedule}>Programma</Button>
                )}
                {actions.canReject && !rejecting && (
                    <Button variant="outline" onClick={startReject}>
                        ✕ Rifiuta
                    </Button>
                )}
                {actions.canEdit && content.status === 'needs_changes' && (
                    <Button variant="outline" onClick={onResumeToDraft}>
                        Riprendi in lavorazione
                    </Button>
                )}
                {actions.canEdit && editInPlace && (
                    <ContentEditDialog content={content} />
                )}
                {actions.canEdit && !editInPlace && (
                    <Button variant="ghost" asChild>
                        <Link href={`/contents/${content.id}/edit`}>
                            Modifica
                        </Link>
                    </Button>
                )}
            </div>

            {rejecting && (
                <div className="space-y-2">
                    <Textarea
                        placeholder="Perché rifiuti questo contenuto? Il commento aiuta il team a capire cosa cambiare."
                        value={rejectComment}
                        onChange={(e) => setRejectComment(e.target.value)}
                    />
                    <div className="flex gap-2">
                        <Button onClick={confirmReject}>Invia rifiuto</Button>
                        <Button
                            variant="ghost"
                            onClick={() => {
                                setRejecting(false);
                                setRejectComment('');
                            }}
                        >
                            Annulla
                        </Button>
                    </div>
                </div>
            )}

            {actions.canComment && (
                <CommentThread
                    comments={content.comments}
                    onSubmit={onSubmitComment}
                />
            )}
        </div>
    );
}
