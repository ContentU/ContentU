export type CalendarDay = {
    date: Date;
    inCurrentMonth: boolean;
};

function startOfDay(date: Date): Date {
    return new Date(date.getFullYear(), date.getMonth(), date.getDate());
}

/** Lunedì della settimana di `date` (locale it-IT: la settimana inizia di lunedì). */
function startOfWeek(date: Date): Date {
    const day = startOfDay(date);
    // getDay(): 0 = domenica ... 6 = sabato. Spostiamo la domenica a fine settimana.
    const offset = (day.getDay() + 6) % 7;
    return addDays(day, -offset);
}

export function addDays(date: Date, n: number): Date {
    const next = new Date(date);
    next.setDate(next.getDate() + n);
    return next;
}

export function addMonths(date: Date, n: number): Date {
    const next = new Date(date);
    next.setDate(1);
    next.setMonth(next.getMonth() + n);
    return next;
}

export function isSameDay(a: Date, b: Date): boolean {
    return (
        a.getFullYear() === b.getFullYear() &&
        a.getMonth() === b.getMonth() &&
        a.getDate() === b.getDate()
    );
}

/**
 * Griglia mensile: 6 righe fisse da 7 giorni (42 celle), a partire dal lunedì
 * della settimana che contiene il primo del mese, così l'altezza della
 * griglia resta costante indipendentemente dal mese mostrato.
 */
export function getMonthGrid(anchor: Date): CalendarDay[] {
    const firstOfMonth = new Date(anchor.getFullYear(), anchor.getMonth(), 1);
    const gridStart = startOfWeek(firstOfMonth);

    return Array.from({ length: 42 }, (_, i) => {
        const date = addDays(gridStart, i);
        return {
            date,
            inCurrentMonth: date.getMonth() === anchor.getMonth(),
        };
    });
}

/** I 7 giorni (lun-dom) della settimana di `anchor`. */
export function getWeekDays(anchor: Date): Date[] {
    const start = startOfWeek(anchor);
    return Array.from({ length: 7 }, (_, i) => addDays(start, i));
}

export function formatMonthLabel(date: Date): string {
    return new Intl.DateTimeFormat('it-IT', {
        month: 'long',
        year: 'numeric',
    }).format(date);
}

export function formatWeekLabel(days: Date[]): string {
    const first = days[0];
    const last = days[days.length - 1];
    const dayFmt = new Intl.DateTimeFormat('it-IT', { day: 'numeric' });
    const monthYearFmt = new Intl.DateTimeFormat('it-IT', {
        month: 'long',
        year: 'numeric',
    });

    if (first.getMonth() === last.getMonth()) {
        return `${dayFmt.format(first)} – ${dayFmt.format(last)} ${monthYearFmt.format(last)}`;
    }

    const shortFmt = new Intl.DateTimeFormat('it-IT', {
        day: 'numeric',
        month: 'short',
    });
    return `${shortFmt.format(first)} – ${dayFmt.format(last)} ${monthYearFmt.format(last)}`;
}
