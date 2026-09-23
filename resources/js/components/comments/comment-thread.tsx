import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import type { ContentComment } from '@/types/content';

type Props = {
    comments: ContentComment[];
    onSubmit?: (body: string) => void;
};

export function CommentThread({ comments, onSubmit }: Props) {
    const [body, setBody] = useState('');

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!body.trim() || !onSubmit) return;
        onSubmit(body.trim());
        setBody('');
    };

    return (
        <div className="space-y-3">
            {comments.length > 0 && (
                <ul className="space-y-2">
                    {comments.map((comment) => (
                        <li
                            key={comment.id}
                            className={cn(
                                'rounded-md p-3 text-sm',
                                comment.authorLabel === 'Cliente'
                                    ? 'bg-brand-rose-tint'
                                    : 'bg-muted',
                            )}
                        >
                            <p className="text-xs font-medium text-muted-foreground">
                                {comment.authorLabel} · {comment.authorName} ·{' '}
                                {comment.createdAtLabel}
                            </p>
                            <p>{comment.body}</p>
                        </li>
                    ))}
                </ul>
            )}

            {comments.length === 0 && (
                <p className="text-sm text-muted-foreground">
                    Nessun commento ancora.
                </p>
            )}

            {onSubmit && (
                <form onSubmit={submit} className="space-y-2">
                    <Textarea
                        placeholder="Scrivi un commento…"
                        value={body}
                        onChange={(e) => setBody(e.target.value)}
                    />
                    <Button type="submit" size="sm" disabled={!body.trim()}>
                        Invia commento
                    </Button>
                </form>
            )}
        </div>
    );
}
