import { useState } from 'react';
import { MonthCalendar } from '@/components/calendar/month-calendar';
import { isSameDay } from '@/lib/calendar-grid';

type Assignment = {
    role: 'photo' | 'video' | 'coordination';
    roleInitial: string;
    userName: string;
    isAlternative: boolean;
};

type Session = {
    id: number;
    date: string;
    clientName: string;
    typeLabel: string;
    assignments: Assignment[];
};

type Props = {
    sessions: Session[];
};

function sessionsOnDay(sessions: Session[], date: Date): Session[] {
    return sessions.filter((s) => isSameDay(new Date(s.date), date));
}

function SessionChip({ session }: { session: Session }) {
    const primary = session.assignments.filter((a) => !a.isAlternative);

    return (
        <div className="rounded-sm bg-muted px-1 py-0.5 text-[11px]">
            <p className="truncate font-medium">{session.clientName}</p>
            <p className="truncate text-muted-foreground">
                {session.typeLabel}
                {primary.length > 0 &&
                    ` · ${primary.map((a) => a.roleInitial).join('')}`}
            </p>
        </div>
    );
}

export function SessionsCalendarView({ sessions }: Props) {
    const [anchor, setAnchor] = useState(() => {
        const first = sessions[0]?.date;
        return first ? new Date(first) : new Date();
    });

    return (
        <MonthCalendar
            anchor={anchor}
            onAnchorChange={setAnchor}
            renderDay={(day) => (
                <>
                    <p className="mb-1 text-[11px] text-muted-foreground">
                        {day.date.getDate()}
                    </p>
                    <div className="space-y-0.5">
                        {sessionsOnDay(sessions, day.date).map((s) => (
                            <SessionChip key={s.id} session={s} />
                        ))}
                    </div>
                </>
            )}
        />
    );
}
