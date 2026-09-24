import { useState } from 'react';
import { MonthCalendar } from '@/components/calendar/month-calendar';
import { WeekCalendar } from '@/components/calendar/week-calendar';
import { Button } from '@/components/ui/button';
import { isSameDay } from '@/lib/calendar-grid';
import { cn } from '@/lib/utils';
import type { FeedContent } from '@/types/content';

type Props = {
    contents: FeedContent[];
    selectedId: number | null;
    onSelect: (id: number) => void;
};

function contentsOnDay(contents: FeedContent[], date: Date): FeedContent[] {
    return contents.filter((c) => isSameDay(new Date(c.publishAt), date));
}

function DayChip({
    content,
    selected,
    onSelect,
}: {
    content: FeedContent;
    selected: boolean;
    onSelect: (id: number) => void;
}) {
    return (
        <button
            type="button"
            onClick={() => onSelect(content.id)}
            aria-pressed={selected}
            className={cn(
                'block w-full truncate rounded-sm px-1 py-0.5 text-left text-[11px]',
                selected ? 'bg-brand-rose-tint' : 'bg-muted hover:bg-accent',
            )}
        >
            {content.typeLabel} · {content.title}
        </button>
    );
}

export function ContentCalendarView({ contents, selectedId, onSelect }: Props) {
    const [mode, setMode] = useState<'month' | 'week'>('month');
    const [anchor, setAnchor] = useState(() => {
        const first = contents[0]?.publishAt;
        return first ? new Date(first) : new Date();
    });

    return (
        <div>
            <div className="mb-3 flex gap-2">
                <Button
                    type="button"
                    size="sm"
                    variant={mode === 'month' ? 'default' : 'outline'}
                    onClick={() => setMode('month')}
                >
                    Mese
                </Button>
                <Button
                    type="button"
                    size="sm"
                    variant={mode === 'week' ? 'default' : 'outline'}
                    onClick={() => setMode('week')}
                >
                    Settimana
                </Button>
            </div>

            {mode === 'month' ? (
                <MonthCalendar
                    anchor={anchor}
                    onAnchorChange={setAnchor}
                    renderDay={(day) => (
                        <>
                            <p className="mb-1 text-[11px] text-muted-foreground">
                                {day.date.getDate()}
                            </p>
                            <div className="space-y-0.5">
                                {contentsOnDay(contents, day.date).map((c) => (
                                    <DayChip
                                        key={c.id}
                                        content={c}
                                        selected={selectedId === c.id}
                                        onSelect={onSelect}
                                    />
                                ))}
                            </div>
                        </>
                    )}
                />
            ) : (
                <WeekCalendar
                    anchor={anchor}
                    onAnchorChange={setAnchor}
                    renderDay={(date) => (
                        <div className="space-y-0.5">
                            {contentsOnDay(contents, date).map((c) => (
                                <DayChip
                                    key={c.id}
                                    content={c}
                                    selected={selectedId === c.id}
                                    onSelect={onSelect}
                                />
                            ))}
                        </div>
                    )}
                />
            )}
        </div>
    );
}
