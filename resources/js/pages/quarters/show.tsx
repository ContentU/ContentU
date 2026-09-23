import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { StatusBadge, type DomainStatus } from '@/components/status-badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Props = {
    client: { id: number; name: string };
    quarter: {
        id: number;
        label: string;
        status: DomainStatus;
        statusLabel: string;
    };
    allowedTransitions: { value: string; label: string }[];
};

export default function QuarterShow({
    client,
    quarter,
    allowedTransitions,
}: Props) {
    const updateStatus = (status: string) => {
        router.patch(`/quarters/${quarter.id}/status`, { status });
    };

    return (
        <AppLayout>
            <Head title={`${client.name} — ${quarter.label}`} />

            <div className="flex items-center justify-between gap-4">
                <div>
                    <p className="text-sm text-muted-foreground">
                        {client.name}
                    </p>
                    <h1 className="font-serif text-2xl">{quarter.label}</h1>
                </div>

                <div className="flex items-center gap-3">
                    <StatusBadge
                        status={quarter.status}
                        label={quarter.statusLabel}
                    />

                    {allowedTransitions.length > 0 && (
                        <Select onValueChange={updateStatus}>
                            <SelectTrigger className="w-48">
                                <SelectValue placeholder="Cambia stato" />
                            </SelectTrigger>
                            <SelectContent>
                                {allowedTransitions.map((t) => (
                                    <SelectItem key={t.value} value={t.value}>
                                        {t.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    )}
                </div>
            </div>

            <div className="mt-8 rounded-lg border border-dashed border-border p-8 text-center text-sm text-muted-foreground">
                I contenuti del trimestre arrivano nella Fase 05.
            </div>
        </AppLayout>
    );
}
