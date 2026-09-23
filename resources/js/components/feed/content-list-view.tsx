/**
 * Vista a elenco richiesta dal capo (commento §3.4 del documento PED).
 * Il layout definitivo verrà fornito da lui: questa è la struttura dati e
 * il comportamento, lo stile è provvisorio. Non duplicare qui la logica di
 * approvazione: usa ContentDetailPanel come fa ContentGridView.
 */
import { cn } from '@/lib/utils';
import type { FeedContent } from '@/types/content';

type Props = {
    contents: FeedContent[];
    selectedId: number | null;
    onSelect: (id: number) => void;
};

export function ContentListView({ contents, selectedId, onSelect }: Props) {
    return (
        <div className="flex flex-col divide-y divide-border">
            {contents.map((content) => (
                <button
                    key={content.id}
                    type="button"
                    onClick={() => onSelect(content.id)}
                    aria-pressed={selectedId === content.id}
                    className={cn(
                        'flex items-center gap-4 p-3 text-left',
                        selectedId === content.id && 'bg-brand-rose-tint',
                    )}
                >
                    <div className="size-14 shrink-0 overflow-hidden rounded-sm bg-muted">
                        {content.resourceUrl && (
                            <img
                                src={content.resourceUrl}
                                alt=""
                                className="size-full object-cover"
                                loading="lazy"
                            />
                        )}
                    </div>

                    <div className="min-w-0 flex-1">
                        <p className="truncate text-sm font-medium">
                            {content.caption ?? content.title}
                        </p>
                        <p className="text-xs text-muted-foreground">
                            {content.typeLabel} · {content.publishAtLabel}
                            {content.hashtags && ` · ${content.hashtags}`}
                        </p>
                    </div>
                </button>
            ))}
        </div>
    );
}
