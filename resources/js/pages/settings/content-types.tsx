import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type ContentType = {
    id: number;
    key: string;
    label: string;
    requiresSecondaryAsset: boolean;
    isActive: boolean;
    sortOrder: number;
};

type Props = {
    contentTypes: ContentType[];
    artifactPromptRules: string;
};

function ArtifactPromptRulesCard({ initialValue }: { initialValue: string }) {
    const [editing, setEditing] = useState(false);
    const { data, setData, put, processing } = useForm({
        value: initialValue,
    });

    return (
        <Card className="space-y-3 p-4">
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="font-medium">
                        Regole prompt artifact Claude
                    </h2>
                    <p className="text-sm text-muted-foreground">
                        Usate per comporre il prompt "Preverifica argomenti con
                        Claude" nella scheda cliente.
                    </p>
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    onClick={() => setEditing((v) => !v)}
                >
                    {editing ? 'Annulla' : 'Modifica'}
                </Button>
            </div>

            {editing ? (
                <div className="space-y-2">
                    <Textarea
                        value={data.value}
                        onChange={(e) => setData('value', e.target.value)}
                        rows={10}
                    />
                    <Button
                        type="button"
                        size="sm"
                        disabled={processing}
                        onClick={() =>
                            put('/settings/artifact-prompt-rules', {
                                onSuccess: () => setEditing(false),
                            })
                        }
                    >
                        Salva
                    </Button>
                </div>
            ) : (
                <p className="text-sm whitespace-pre-line text-muted-foreground">
                    {data.value}
                </p>
            )}
        </Card>
    );
}

export default function ContentTypesSettings({
    contentTypes,
    artifactPromptRules,
}: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        key: '',
        label: '',
        requires_secondary_asset: false,
        is_active: true,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/settings/content-types', { onSuccess: () => reset() });
    };

    const toggle = (
        type: ContentType,
        field: 'requires_secondary_asset' | 'is_active',
    ) => {
        router.put(`/settings/content-types/${type.id}`, {
            key: type.key,
            label: type.label,
            requires_secondary_asset:
                field === 'requires_secondary_asset'
                    ? !type.requiresSecondaryAsset
                    : type.requiresSecondaryAsset,
            is_active: field === 'is_active' ? !type.isActive : type.isActive,
        });
    };

    const remove = (type: ContentType) => {
        router.delete(`/settings/content-types/${type.id}`);
    };

    return (
        <>
            <Head title="Tipologie di contenuto" />

            <h1 className="sr-only">Tipologie di contenuto</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Tipologie di contenuto"
                    description="Tutte le tipologie sono gestite qui: non esiste un elenco chiuso nel codice."
                />

                <Card className="divide-y divide-border">
                    {contentTypes.map((type) => (
                        <div
                            key={type.id}
                            className="flex flex-wrap items-center gap-4 p-4"
                        >
                            <div className="min-w-32 flex-1">
                                <p className="font-medium">{type.label}</p>
                                <p className="text-sm text-muted-foreground">
                                    {type.key}
                                </p>
                            </div>

                            <label className="flex items-center gap-2 text-sm">
                                <Checkbox
                                    checked={type.requiresSecondaryAsset}
                                    onCheckedChange={() =>
                                        toggle(type, 'requires_secondary_asset')
                                    }
                                />
                                Richiede asset secondario
                            </label>

                            <label className="flex items-center gap-2 text-sm">
                                <Checkbox
                                    checked={type.isActive}
                                    onCheckedChange={() =>
                                        toggle(type, 'is_active')
                                    }
                                />
                                Attiva
                            </label>

                            <Button
                                type="button"
                                variant="ghost"
                                className="text-destructive"
                                onClick={() => remove(type)}
                            >
                                Elimina
                            </Button>
                        </div>
                    ))}

                    {contentTypes.length === 0 && (
                        <p className="p-4 text-sm text-muted-foreground">
                            Nessuna tipologia configurata.
                        </p>
                    )}
                </Card>

                <form
                    onSubmit={submit}
                    className="flex max-w-lg flex-wrap items-end gap-3"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="key">Chiave</Label>
                        <Input
                            id="key"
                            value={data.key}
                            onChange={(e) => setData('key', e.target.value)}
                        />
                        {errors.key && (
                            <p className="text-sm text-destructive">
                                {errors.key}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="label">Etichetta</Label>
                        <Input
                            id="label"
                            value={data.label}
                            onChange={(e) => setData('label', e.target.value)}
                        />
                        {errors.label && (
                            <p className="text-sm text-destructive">
                                {errors.label}
                            </p>
                        )}
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <Checkbox
                            checked={data.requires_secondary_asset}
                            onCheckedChange={(v) =>
                                setData('requires_secondary_asset', !!v)
                            }
                        />
                        Richiede asset secondario
                    </label>
                    <Button type="submit" disabled={processing}>
                        Aggiungi tipologia
                    </Button>
                </form>

                <ArtifactPromptRulesCard initialValue={artifactPromptRules} />
            </div>
        </>
    );
}
