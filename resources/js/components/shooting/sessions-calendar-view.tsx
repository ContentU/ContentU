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
    clientName?: string;
    typeLabel: string;
    assignments?: Assignment[];
    /** Pallino verde in calendario: usato nel PED pubblico (Fase 07). */
    isApproved?: boolean;
};

type Props<T extends Session> = {
    sessions: T[];
    onSelect?: (session: T) => void;
};

function sessionsOnDay<T extends Session>(sessions: T[], date: Date): T[] {
    return sessions.filter((s) => isSameDay(new Date(s.date), date));
}

function SessionChip<T extends Session>({
    session,
    onSelect,
}: {
    session: T;
    onSelect?: (session: T) => void;
}) {
    const primary = session.assignments?.filter((a) => !a.isAlternative) ?? [];

    const content = (
        <>
            <p className="flex items-center gap-1 truncate font-medium">
                {session.isApproved && (
                    <span className="inline-block size-1.5 shrink-0 rounded-full bg-status-approved" />
                )}
                {session.clientName ?? session.typeLabel}
            </p>
            <p className="truncate text-muted-foreground">
                {session.typeLabel}
                {primary.length > 0 &&
                    ` · ${primary.map((a) => a.roleInitial).join('')}`}
            </p>
        </>
    );

    if (onSelect) {
        return (
            <button
                type="button"
                onClick={() => onSelect(session)}
                className="w-full rounded-sm bg-muted px-1 py-0.5 text-left text-[11px] hover:bg-muted/70"
            >
                {content}
            </button>
        );
    }

    return (
        <div className="rounded-sm bg-muted px-1 py-0.5 text-[11px]">
            {content}
        </div>
    );
}

export function SessionsCalendarView<T extends Session>({
    sessions,
    onSelect,
}: Props<T>) {
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
                            <SessionChip
                                key={s.id}
                                session={s}
                                onSelect={onSelect}
                            />
                        ))}
                    </div>
                </>
            )}
        />
    );
}
