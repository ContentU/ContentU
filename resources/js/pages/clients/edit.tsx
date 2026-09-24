import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
import {
    ClientForm,
    type AssignableUser,
    type EditableClient,
} from '@/pages/clients/form';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type PublicLink = {
    id: number;
    url: string;
    createdAt: string;
};

type AccessUser = {
    id: number;
    email: string;
    hasSetPassword: boolean;
    invitedAt: string;
};

type Props = {
    client: EditableClient;
    assignableUsers: AssignableUser[];
    publicLink: PublicLink | null;
    accessUsers: AccessUser[];
};

function PublicLinkPanel({
    clientId,
    publicLink,
}: {
    clientId: number;
    publicLink: PublicLink | null;
}) {
    const [copied, setCopied] = useState(false);

    const generate = () => {
        router.post(`/clients/${clientId}/public-link`);
    };

    const revoke = () => {
        if (!publicLink) return;
        router.delete(`/public-links/${publicLink.id}`);
    };

    const copy = () => {
        if (!publicLink) return;
        void navigator.clipboard.writeText(publicLink.url);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <Card className="max-w-2xl space-y-3 p-4">
            <h2 className="font-medium">Link pubblico</h2>

            {publicLink ? (
                <>
                    <a
                        href={publicLink.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-sm break-all text-primary underline underline-offset-2 hover:text-primary/80"
                    >
                        {publicLink.url}
                    </a>
                    <p className="text-xs text-muted-foreground">
                        Generato il {publicLink.createdAt}
                    </p>
                    <div className="flex gap-2">
                        <Button type="button" variant="outline" onClick={copy}>
                            {copied ? 'Copiato!' : 'Copia'}
                        </Button>
                        <Button
                            type="button"
                            variant="ghost"
                            className="text-destructive"
                            onClick={revoke}
                        >
                            Revoca
                        </Button>
                    </div>
                </>
            ) : (
                <>
                    <p className="text-sm text-muted-foreground">
                        Nessun link pubblico attivo per questo cliente.
                    </p>
                    <Button type="button" onClick={generate}>
                        Genera link
                    </Button>
                </>
            )}
        </Card>
    );
}

function ClientAccessPanel({
    clientId,
    accessUsers,
}: {
    clientId: number;
    accessUsers: AccessUser[];
}) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/clients/${clientId}/access`, {
            preserveScroll: true,
            onSuccess: () => reset('email'),
        });
    };

    const resend = (userId: number) => {
        router.post(
            `/clients/${clientId}/access/${userId}/resend`,
            {},
            { preserveScroll: true },
        );
    };

    const remove = (userId: number) => {
        router.delete(`/clients/${clientId}/access/${userId}`, {
            preserveScroll: true,
        });
    };

    return (
        <Card className="max-w-2xl space-y-4 p-4">
            <div>
                <h2 className="font-medium">Accesso al PED</h2>
                <p className="text-sm text-muted-foreground">
                    Solo le email autorizzate qui sotto possono accedere al PED
                    di questo cliente.
                </p>
            </div>

            <form onSubmit={submit} className="flex items-start gap-2">
                <div className="grid flex-1 gap-1">
                    <Label htmlFor="access-email" className="sr-only">
                        Email da autorizzare
                    </Label>
                    <Input
                        id="access-email"
                        type="email"
                        placeholder="email@cliente.it"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    <InputError message={errors.email} />
                </div>
                <Button type="submit" disabled={processing}>
                    Aggiungi
                </Button>
            </form>

            {accessUsers.length > 0 && (
                <ul className="divide-y">
                    {accessUsers.map((user) => (
                        <li
                            key={user.id}
                            className="flex items-center justify-between gap-3 py-2"
                        >
                            <div>
                                <p className="text-sm">{user.email}</p>
                                <p className="text-xs text-muted-foreground">
                                    {user.hasSetPassword
                                        ? 'Attivo'
                                        : 'Invito inviato'}
                                    {' · '}
                                    Aggiunto il {user.invitedAt}
                                </p>
                            </div>
                            <div className="flex shrink-0 gap-2">
                                {!user.hasSetPassword && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() => resend(user.id)}
                                    >
                                        Reinvia invito
                                    </Button>
                                )}
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    className="text-destructive"
                                    onClick={() => remove(user.id)}
                                >
                                    Rimuovi
                                </Button>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </Card>
    );
}

function ArtifactLinkField({
    label,
    url,
    onChange,
    error,
}: {
    label: string;
    url: string;
    onChange: (url: string) => void;
    error?: string;
}) {
    const copyLink = () => {
        void navigator.clipboard.writeText(url);
        toast.success('Link copiato');
    };

    return (
        <div className="grid gap-2">
            <Label>{label}</Label>
            <div className="flex flex-wrap items-center gap-2">
                <Input
                    type="url"
                    value={url}
                    onChange={(e) => onChange(e.target.value)}
                    placeholder="https://claude.ai/public/artifacts/…"
                    className="max-w-md"
                />
                {url && (
                    <>
                        <a
                            href={url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-sm text-primary underline underline-offset-2 hover:text-primary/80"
                        >
                            Apri
                        </a>
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            onClick={copyLink}
                        >
                            Copia link
                        </Button>
                    </>
                )}
            </div>
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}

function ArtifactLinksPanel({
    clientId,
    topicsArtifactUrl,
    shootingArtifactUrl,
}: {
    clientId: number;
    topicsArtifactUrl: string | null;
    shootingArtifactUrl: string | null;
}) {
    const { data, setData, patch, processing, errors } = useForm({
        topics_artifact_url: topicsArtifactUrl ?? '',
        shooting_artifact_url: shootingArtifactUrl ?? '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(`/clients/${clientId}/artifact-links`, {
            preserveScroll: true,
        });
    };

    return (
        <Card className="max-w-2xl space-y-4 p-4">
            <div>
                <h2 className="font-medium">Link artifact Claude</h2>
                <p className="text-sm text-muted-foreground">
                    In Claude: crea l&apos;artifact → Condividi → copia il link
                    pubblico.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-4">
                <ArtifactLinkField
                    label="Presentazione argomenti"
                    url={data.topics_artifact_url}
                    onChange={(url) => setData('topics_artifact_url', url)}
                    error={errors.topics_artifact_url}
                />

                <ArtifactLinkField
                    label="Strategia shooting"
                    url={data.shooting_artifact_url}
                    onChange={(url) => setData('shooting_artifact_url', url)}
                    error={errors.shooting_artifact_url}
                />

                <Button type="submit" disabled={processing}>
                    Salva
                </Button>
            </form>
        </Card>
    );
}

export default function ClientsEdit({
    client,
    assignableUsers,
    publicLink,
    accessUsers,
}: Props) {
    return (
        <>
            <Head title={`Modifica ${client.name}`} />

            <div className="p-4 md:p-6">
                <h1 className="font-serif text-2xl">Modifica {client.name}</h1>

                <div className="mt-6">
                    <ClientForm
                        client={client}
                        assignableUsers={assignableUsers}
                    />
                </div>

                <div className="mt-8">
                    <PublicLinkPanel
                        clientId={client.id}
                        publicLink={publicLink}
                    />
                </div>

                <div className="mt-8">
                    <ArtifactLinksPanel
                        clientId={client.id}
                        topicsArtifactUrl={client.topicsArtifactUrl}
                        shootingArtifactUrl={client.shootingArtifactUrl}
                    />
                </div>

                <div className="mt-8">
                    <ClientAccessPanel
                        clientId={client.id}
                        accessUsers={accessUsers}
                    />
                </div>
            </div>
        </>
    );
}
