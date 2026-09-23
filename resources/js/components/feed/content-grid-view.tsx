import { cn } from '@/lib/utils';
import type { FeedContent } from '@/types/content';

type Props = {
    contents: FeedContent[];
    selectedId: number | null;
    onSelect: (id: number) => void;
};

export function ContentGridView({ contents, selectedId, onSelect }: Props) {
    return (
        <div className="grid grid-cols-3 gap-1">
            {contents.map((content) => (
                <button
                    key={content.id}
                    type="button"
                    onClick={() => onSelect(content.id)}
                    aria-pressed={selectedId === content.id}
                    aria-label={content.title}
                    className={cn(
                        'relative aspect-square overflow-hidden rounded-sm bg-muted text-left',
                        content.isToday && 'ring-2 ring-primary',
                        selectedId === content.id && 'bg-brand-rose-tint',
                    )}
                >
                    {content.resourceUrl ? (
                        <img
                            src={content.resourceUrl}
                            alt=""
                            className="size-full object-cover"
                            loading="lazy"
                        />
                    ) : (
                        <div className="flex size-full items-center justify-center text-xs text-muted-foreground">
                            {content.typeLabel}
                        </div>
                    )}
                    <span className="absolute left-1 top-1 rounded-sm bg-background/85 px-1 font-mono text-[10px] uppercase tracking-wide">
                        {content.typeLabel}
                    </span>
                    {content.isToday && (
                        <span className="absolute bottom-1 left-1 rounded-sm bg-primary px-1 font-mono text-[10px] uppercase text-primary-foreground">
                            oggi
                        </span>
                    )}
                </button>
            ))}
        </div>
    );
}
