import { ChevronLeftIcon, ChevronRightIcon } from 'lucide-react';
import { addDays, formatWeekLabel, getWeekDays } from '@/lib/calendar-grid';
import { Button } from '@/components/ui/button';

const WEEKDAY_LABELS = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];

type Props = {
    anchor: Date;
    onAnchorChange: (date: Date) => void;
    renderDay: (date: Date) => React.ReactNode;
};

export function WeekCalendar({ anchor, onAnchorChange, renderDay }: Props) {
    const days = getWeekDays(anchor);

    return (
        <div>
            <div className="mb-3 flex items-center justify-between">
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    aria-label="Settimana precedente"
                    onClick={() => onAnchorChange(addDays(anchor, -7))}
                >
                    <ChevronLeftIcon />
                </Button>
                <span className="font-medium capitalize">
                    {formatWeekLabel(days)}
                </span>
                <Button
                    type="button"
                    variant="outline"
                    size="icon"
                    aria-label="Settimana successiva"
                    onClick={() => onAnchorChange(addDays(anchor, 7))}
                >
                    <ChevronRightIcon />
                </Button>
            </div>

            <div className="grid grid-cols-7 gap-px">
                {days.map((date, i) => (
                    <div key={i} className="min-h-40 border border-border p-1">
                        <p className="mb-1 text-xs text-muted-foreground">
                            {WEEKDAY_LABELS[i]} {date.getDate()}
                        </p>
                        {renderDay(date)}
                    </div>
                ))}
            </div>
        </div>
    );
}
