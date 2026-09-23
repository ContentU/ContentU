import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { StatusBadge, type DomainStatus } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type QuarterContent = {
    id: number;
    title: string;
    contentTypeLabel: string;
    publishAt: string;
    status: DomainStatus;
    statusLabel: string;
    channels: string[];
    isReady: boolean;
};

type Props = {
    client: { id: number; name: string };
    quarter: {
        id: number;
        label: string;
        status: DomainStatus;
        statusLabel: string;
    };
    allowedTransitions: { value: string; label: string }[];
    contents: QuarterContent[];
};

export default function QuarterShow({
    client,
    quarter,
    allowedTransitions,
    contents,
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

            <div className="mt-8 flex items-center justify-between">
                <h2 className="font-serif text-xl">Contenuti</h2>
                <div className="flex gap-2">
                    <Button asChild variant="outline">
                        <Link href={`/quarters/${quarter.id}/feed`}>
                            Vedi feed
                        </Link>
                    </Button>
                    <Button asChild variant="outline">
                        <Link href={`/quarters/${quarter.id}/contents/create`}>
                            Nuovo contenuto
                        </Link>
                    </Button>
                </div>
            </div>

            <div className="mt-4 space-y-2">
                {contents.map((content) => (
                    <Card
                        key={content.id}
                        className="flex flex-wrap items-center gap-4 p-4"
                    >
                        <Link
                            href={`/contents/${content.id}/edit`}
                            className="min-w-40 flex-1 font-medium hover:underline"
                        >
                            {content.title}
                        </Link>
                        <span className="text-sm text-muted-foreground">
                            {content.contentTypeLabel}
                        </span>
                        <span className="text-sm text-muted-foreground">
                            {content.publishAt}
                        </span>
                        <span className="text-sm text-muted-foreground">
                            {content.channels.join(', ') || 'Nessun canale'}
                        </span>
                        <StatusBadge
                            status={content.status}
                            label={content.statusLabel}
                        />
                        {!content.isReady && (
                            <span className="text-sm text-destructive">
                                Incompleto
                            </span>
                        )}
                    </Card>
                ))}

                {contents.length === 0 && (
                    <p className="mt-6 text-center text-sm text-muted-foreground">
                        Nessun contenuto per questo trimestre.
                    </p>
                )}
            </div>
        </AppLayout>
    );
}
