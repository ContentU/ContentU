import { Head, Link, useForm } from '@inertiajs/react';
import { StatusBadge, type DomainStatus } from '@/components/status-badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Props = {
    client: { id: number; name: string };
    quarters: {
        id: number;
        label: string;
        status: DomainStatus;
        statusLabel: string;
    }[];
};

export default function QuartersIndex({ client, quarters }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        year: new Date().getFullYear(),
        quarter_number: 1,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/clients/${client.id}/quarters`);
    };

    return (
        <>
            <Head title={`Trimestri — ${client.name}`} />

            <div className="p-4 md:p-6">
                <h1 className="font-serif text-2xl">
                    Trimestri — {client.name}
                </h1>

                <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {quarters.map((quarter) => (
                        <Card key={quarter.id} className="p-4">
                            <Link
                                href={`/quarters/${quarter.id}`}
                                className="font-medium hover:underline"
                            >
                                {quarter.label}
                            </Link>
                            <div className="mt-2">
                                <StatusBadge
                                    status={quarter.status}
                                    label={quarter.statusLabel}
                                />
                            </div>
                        </Card>
                    ))}
                </div>

                {quarters.length === 0 && (
                    <p className="mt-10 text-center text-muted-foreground">
                        Nessun trimestre per questo cliente.
                    </p>
                )}

                <form
                    onSubmit={submit}
                    className="mt-8 flex max-w-sm items-end gap-3"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="year">Anno</Label>
                        <Input
                            id="year"
                            type="number"
                            value={data.year}
                            onChange={(e) =>
                                setData('year', Number(e.target.value))
                            }
                        />
                        {errors.year && (
                            <p className="text-sm text-destructive">
                                {errors.year}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="quarter_number">Trimestre</Label>
                        <Input
                            id="quarter_number"
                            type="number"
                            min={1}
                            max={4}
                            value={data.quarter_number}
                            onChange={(e) =>
                                setData(
                                    'quarter_number',
                                    Number(e.target.value),
                                )
                            }
                        />
                        {errors.quarter_number && (
                            <p className="text-sm text-destructive">
                                {errors.quarter_number}
                            </p>
                        )}
                    </div>
                    <Button type="submit" disabled={processing}>
                        Aggiungi trimestre
                    </Button>
                </form>
            </div>
        </>
    );
}
