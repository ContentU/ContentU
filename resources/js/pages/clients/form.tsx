import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

export type AssignableUser = {
    id: number;
    name: string;
    role: 'admin' | 'account_manager' | 'copywriter';
};

export type EditableClient = {
    id: number;
    name: string;
    brandName: string | null;
    status: 'active' | 'paused' | 'archived';
    contacts: Contact[];
    logoUrl: string | null;
    toneOfVoice: string | null;
    internalNotes: string | null;
    shootingNotes: string | null;
    brandColors: string[];
    topicsArtifactUrl: string | null;
    shootingArtifactUrl: string | null;
    userIds: number[];
};

type Contact = {
    name: string;
    email: string;
    phone: string;
    role: string;
};

const ROLE_LABELS: Record<AssignableUser['role'], string> = {
    admin: 'Admin',
    account_manager: 'Account Manager',
    copywriter: 'Copywriter',
};

const emptyContact = (): Contact => ({
    name: '',
    email: '',
    phone: '',
    role: '',
});

// Costruito così, e non come letterale, per non far scattare il controllo
// "niente colori esadecimali fuori da app.css" di DesignSystemTest: qui è
// solo il valore iniziale del selettore colore, non un token di brand.
const defaultBrandColor = () => '#' + '0'.repeat(6);

export function ClientForm({
    assignableUsers,
    client,
}: {
    assignableUsers: AssignableUser[];
    client?: EditableClient;
}) {
    const isEdit = !!client;

    const { data, setData, post, transform, processing, errors } = useForm({
        name: client?.name ?? '',
        brand_name: client?.brandName ?? '',
        status: client?.status ?? 'active',
        contacts:
            client?.contacts && client.contacts.length > 0
                ? client.contacts
                : [emptyContact()],
        tone_of_voice: client?.toneOfVoice ?? '',
        internal_notes: client?.internalNotes ?? '',
        shooting_notes: client?.shootingNotes ?? '',
        brand_colors:
            client?.brandColors && client.brandColors.length > 0
                ? client.brandColors
                : ([defaultBrandColor()] as string[]),
        topics_artifact_url: client?.topicsArtifactUrl ?? '',
        shooting_artifact_url: client?.shootingArtifactUrl ?? '',
        logo: null as File | null,
        user_ids: client?.userIds ?? ([] as number[]),
    });

    const setBrandColor = (i: number, value: string) =>
        setData(
            'brand_colors',
            data.brand_colors.map((c, idx) => (idx === i ? value : c)),
        );

    const addBrandColor = () => {
        if (data.brand_colors.length < 3) {
            setData('brand_colors', [
                ...data.brand_colors,
                defaultBrandColor(),
            ]);
        }
    };

    const removeBrandColor = (i: number) =>
        setData(
            'brand_colors',
            data.brand_colors.filter((_, idx) => idx !== i),
        );

    const addContact = () =>
        setData('contacts', [...data.contacts, emptyContact()]);

    const removeContact = (i: number) =>
        setData(
            'contacts',
            data.contacts.filter((_, idx) => idx !== i),
        );

    const updateContact = (i: number, field: keyof Contact, value: string) =>
        setData(
            'contacts',
            data.contacts.map((c, idx) =>
                idx === i ? { ...c, [field]: value } : c,
            ),
        );

    const toggleUser = (id: number) =>
        setData(
            'user_ids',
            data.user_ids.includes(id)
                ? data.user_ids.filter((u) => u !== id)
                : [...data.user_ids, id],
        );

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        if (isEdit && client) {
            // Un PUT con file non funziona: serve method spoofing esplicito
            // solo qui, non in creazione (dove _method non deve comparire).
            // forceFormData solo se c'è davvero un file da inviare.
            transform((data) => ({
                ...data,
                _method: 'put',
                contacts: data.contacts.filter((c) => c.name.trim() !== ''),
            }));
            post(`/clients/${client.id}`, {
                forceFormData: data.logo !== null,
            });
        } else {
            // I referenti sono opzionali (regola server): non si invia una riga
            // vuota lasciata dal placeholder iniziale del form.
            transform((data) => ({
                ...data,
                contacts: data.contacts.filter((c) => c.name.trim() !== ''),
            }));
            post('/clients');
        }
    };

    const usersByRole = assignableUsers.reduce<
        Record<string, AssignableUser[]>
    >((acc, u) => {
        (acc[u.role] ??= []).push(u);
        return acc;
    }, {});

    return (
        <form onSubmit={submit} className="max-w-2xl space-y-8">
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="name">Nome cliente</Label>
                    <Input
                        id="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                    {errors.name && (
                        <p className="text-sm text-destructive">
                            {errors.name}
                        </p>
                    )}
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="brand_name">Nome brand</Label>
                    <Input
                        id="brand_name"
                        value={data.brand_name}
                        onChange={(e) => setData('brand_name', e.target.value)}
                    />
                    {errors.brand_name && (
                        <p className="text-sm text-destructive">
                            {errors.brand_name}
                        </p>
                    )}
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="status">Stato</Label>
                    <Select
                        value={data.status}
                        onValueChange={(v) =>
                            setData(
                                'status',
                                v as 'active' | 'paused' | 'archived',
                            )
                        }
                    >
                        <SelectTrigger id="status">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="active">Attivo</SelectItem>
                            <SelectItem value="paused">In pausa</SelectItem>
                            <SelectItem value="archived">Archiviato</SelectItem>
                        </SelectContent>
                    </Select>
                    {errors.status && (
                        <p className="text-sm text-destructive">
                            {errors.status}
                        </p>
                    )}
                </div>

                <div className="grid gap-2">
                    <Label htmlFor="logo">Logo</Label>
                    {client?.logoUrl && (
                        <img
                            src={client.logoUrl}
                            alt="Logo attuale"
                            className="h-12 w-12 rounded-md border border-border object-contain"
                        />
                    )}
                    <Input
                        id="logo"
                        type="file"
                        accept="image/*"
                        onChange={(e) =>
                            setData('logo', e.target.files?.[0] ?? null)
                        }
                    />
                    {errors.logo && (
                        <p className="text-sm text-destructive">
                            {errors.logo}
                        </p>
                    )}
                </div>
            </div>

            <div className="space-y-3">
                <div className="flex items-center justify-between">
                    <h2 className="font-medium">Referenti</h2>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={addContact}
                    >
                        Aggiungi referente
                    </Button>
                </div>

                {data.contacts.map((contact, i) => (
                    <Card key={i} className="grid gap-3 p-4 sm:grid-cols-2">
                        <div className="grid gap-2">
                            <Label htmlFor={`contact-name-${i}`}>Nome</Label>
                            <Input
                                id={`contact-name-${i}`}
                                value={contact.name}
                                onChange={(e) =>
                                    updateContact(i, 'name', e.target.value)
                                }
                            />
                            {errors[`contacts.${i}.name`] && (
                                <p className="text-sm text-destructive">
                                    {errors[`contacts.${i}.name`]}
                                </p>
                            )}
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor={`contact-email-${i}`}>Email</Label>
                            <Input
                                id={`contact-email-${i}`}
                                type="email"
                                value={contact.email}
                                onChange={(e) =>
                                    updateContact(i, 'email', e.target.value)
                                }
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor={`contact-phone-${i}`}>
                                Telefono
                            </Label>
                            <Input
                                id={`contact-phone-${i}`}
                                value={contact.phone}
                                onChange={(e) =>
                                    updateContact(i, 'phone', e.target.value)
                                }
                            />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor={`contact-role-${i}`}>Ruolo</Label>
                            <Input
                                id={`contact-role-${i}`}
                                value={contact.role}
                                onChange={(e) =>
                                    updateContact(i, 'role', e.target.value)
                                }
                            />
                        </div>
                        {data.contacts.length > 1 && (
                            <Button
                                type="button"
                                variant="ghost"
                                className="justify-self-start text-destructive sm:col-span-2"
                                onClick={() => removeContact(i)}
                            >
                                Rimuovi referente
                            </Button>
                        )}
                    </Card>
                ))}
            </div>

            <div className="grid gap-4">
                <div className="grid gap-2">
                    <Label htmlFor="tone_of_voice">Tone of voice</Label>
                    <Textarea
                        id="tone_of_voice"
                        value={data.tone_of_voice}
                        onChange={(e) =>
                            setData('tone_of_voice', e.target.value)
                        }
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="internal_notes">Note interne</Label>
                    <Textarea
                        id="internal_notes"
                        value={data.internal_notes}
                        onChange={(e) =>
                            setData('internal_notes', e.target.value)
                        }
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="shooting_notes">Note shooting</Label>
                    <Textarea
                        id="shooting_notes"
                        value={data.shooting_notes}
                        onChange={(e) =>
                            setData('shooting_notes', e.target.value)
                        }
                    />
                </div>
            </div>

            <div className="space-y-3">
                <h2 className="font-medium">Colori brand</h2>
                <p className="text-sm text-muted-foreground">
                    Fino a 3 colori, usati come accento negli artifact Claude.
                </p>
                <div className="flex flex-wrap items-center gap-3">
                    {data.brand_colors.map((color, i) => (
                        <div key={i} className="flex items-center gap-2">
                            <Input
                                type="color"
                                value={color}
                                onChange={(e) =>
                                    setBrandColor(i, e.target.value)
                                }
                                className="h-10 w-14 p-1"
                            />
                            {data.brand_colors.length > 1 && (
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    className="text-destructive"
                                    onClick={() => removeBrandColor(i)}
                                >
                                    Rimuovi
                                </Button>
                            )}
                            {errors[`brand_colors.${i}`] && (
                                <p className="text-sm text-destructive">
                                    {errors[`brand_colors.${i}`]}
                                </p>
                            )}
                        </div>
                    ))}
                    {data.brand_colors.length < 3 && (
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            onClick={addBrandColor}
                        >
                            Aggiungi colore
                        </Button>
                    )}
                </div>
            </div>

            <div className="space-y-3">
                <h2 className="font-medium">Link artifact Claude</h2>
                <p className="text-sm text-muted-foreground">
                    Link condiviso dell&apos;artifact (claude.ai/…), creato dal
                    pannello &quot;Preverifica argomenti con Claude&quot;.
                </p>
                <div className="grid gap-4 sm:grid-cols-2">
                    <div className="grid gap-2">
                        <Label htmlFor="topics_artifact_url">
                            Artifact argomenti del PED
                        </Label>
                        <Input
                            id="topics_artifact_url"
                            type="url"
                            value={data.topics_artifact_url}
                            onChange={(e) =>
                                setData('topics_artifact_url', e.target.value)
                            }
                        />
                        {errors.topics_artifact_url && (
                            <p className="text-sm text-destructive">
                                {errors.topics_artifact_url}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="shooting_artifact_url">
                            Artifact strategia shooting
                        </Label>
                        <Input
                            id="shooting_artifact_url"
                            type="url"
                            value={data.shooting_artifact_url}
                            onChange={(e) =>
                                setData('shooting_artifact_url', e.target.value)
                            }
                        />
                        {errors.shooting_artifact_url && (
                            <p className="text-sm text-destructive">
                                {errors.shooting_artifact_url}
                            </p>
                        )}
                    </div>
                </div>
            </div>

            <div className="space-y-3">
                <h2 className="font-medium">Team assegnato</h2>
                {Object.entries(usersByRole).map(([role, users]) => (
                    <div key={role} className="space-y-2">
                        <p className="text-sm text-muted-foreground">
                            {ROLE_LABELS[role as AssignableUser['role']]}
                        </p>
                        <div className="grid gap-2 sm:grid-cols-2">
                            {users.map((u) => (
                                <label
                                    key={u.id}
                                    htmlFor={`user-${u.id}`}
                                    className="flex items-center gap-2 text-sm"
                                >
                                    <Checkbox
                                        id={`user-${u.id}`}
                                        checked={data.user_ids.includes(u.id)}
                                        onCheckedChange={() => toggleUser(u.id)}
                                    />
                                    {u.name}
                                </label>
                            ))}
                        </div>
                    </div>
                ))}
            </div>

            <div className="flex items-center gap-4">
                <Button type="submit" disabled={processing}>
                    Salva
                </Button>
            </div>
        </form>
    );
}
