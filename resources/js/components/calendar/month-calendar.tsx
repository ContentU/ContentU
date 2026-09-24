import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-react';
import {
    addMonths,
    type CalendarDay,
    formatMonthLabel,
    getMonthGrid,
} from '@/lib/calendar-grid';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';

const WEEKDAY_LABELS = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];

type Props = {
    anchor: Date;
    onAnchorChange: (date: Date) => void;
    renderDay: (day: CalendarDay) => React.ReactNode;
};

export function MonthCalendar({ anchor, onAnchorChange, renderDay }: Props) {
    const days = getMonthGrid(anchor);

    return (
        <div>
            <div className="mb-3 flex items-center justify-between">
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    aria-label="Mese precedente"
                    onClick={() => onAnchorChange(addMonths(anchor, -1))}
                >
                    <ChevronLeftIcon />
                </Button>
                <span className="font-medium capitalize">
                    {formatMonthLabel(anchor)}
                </span>
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    aria-label="Mese successivo"
                    onClick={() => onAnchorChange(addMonths(anchor, 1))}
                >
                    <ChevronRightIcon />
                </Button>
            </div>

            <div className="grid grid-cols-7 gap-px text-xs text-muted-foreground">
                {WEEKDAY_LABELS.map((d) => (
                    <div key={d} className="p-1 text-center">
                        {d}
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-7 gap-px">
                {days.map((day, i) => (
                    <div
                        key={i}
                        className={cn(
                            'min-h-20 border border-border p-1',
                            !day.inCurrentMonth && 'opacity-40',
                        )}
                    >
                        {renderDay(day)}
                    </div>
                ))}
            </div>
        </div>
    );
}
