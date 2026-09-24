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
import { Textarea } from '@/components/ui/textarea';

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

type ArtifactPrompts = {
    topics: string;
    shooting: string;
};

type Props = {
    client: EditableClient;
    assignableUsers: AssignableUser[];
    publicLink: PublicLink | null;
    accessUsers: AccessUser[];
    artifactPrompts: ArtifactPrompts;
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

function ArtifactBlock({
    title,
    initialPrompt,
    artifactUrl,
    onSaveUrl,
    savingUrl,
    urlError,
}: {
    title: string;
    initialPrompt: string;
    artifactUrl: string;
    onSaveUrl: (url: string) => void;
    savingUrl: boolean;
    urlError?: string;
}) {
    const [prompt, setPrompt] = useState(initialPrompt);
    const [url, setUrl] = useState(artifactUrl);

    const copyAndOpen = () => {
        void navigator.clipboard.writeText(prompt);
        window.open('https://claude.ai/new', '_blank', 'noopener');
        toast.success('Prompt copiato: incollalo in Claude');
    };

    const copyLink = () => {
        void navigator.clipboard.writeText(url);
        toast.success('Link copiato');
    };

    return (
        <div className="space-y-3">
            <h3 className="font-medium">{title}</h3>

            <Textarea
                value={prompt}
                onChange={(e) => setPrompt(e.target.value)}
                rows={8}
                className="font-mono text-xs"
            />

            <Button type="button" onClick={copyAndOpen}>
                Copia prompt e apri Claude
            </Button>

            <div className="grid gap-2 pt-2">
                <Label>Link artifact condiviso</Label>
                <div className="flex flex-wrap items-center gap-2">
                    <Input
                        type="url"
                        value={url}
                        onChange={(e) => setUrl(e.target.value)}
                        placeholder="https://claude.ai/public/artifacts/…"
                        className="max-w-md"
                    />
                    <Button
                        type="button"
                        variant="outline"
                        disabled={savingUrl}
                        onClick={() => onSaveUrl(url)}
                    >
                        Salva
                    </Button>
                    {artifactUrl && (
                        <>
                            <a
                                href={artifactUrl}
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
                {urlError && (
                    <p className="text-sm text-destructive">{urlError}</p>
                )}
            </div>
        </div>
    );
}

function ArtifactPanel({
    clientId,
    artifactPrompts,
    topicsArtifactUrl,
    shootingArtifactUrl,
}: {
    clientId: number;
    artifactPrompts: ArtifactPrompts;
    topicsArtifactUrl: string | null;
    shootingArtifactUrl: string | null;
}) {
    const { setData, patch, transform, processing, errors } = useForm({
        topics_artifact_url: topicsArtifactUrl ?? '',
        shooting_artifact_url: shootingArtifactUrl ?? '',
    });

    const save = (
        field: 'topics_artifact_url' | 'shooting_artifact_url',
        url: string,
    ) => {
        setData(field, url);
        // transform legge lo stato aggiornato sincrono al momento dell'invio,
        // evitando la corsa tra setData (asincrono) e patch().
        transform((data) => ({ ...data, [field]: url }));
        patch(`/clients/${clientId}/artifact-links`, { preserveScroll: true });
    };

    return (
        <Card className="max-w-2xl space-y-6 p-4">
            <div>
                <h2 className="font-medium">
                    Preverifica argomenti con Claude
                </h2>
                <p className="text-sm text-muted-foreground">
                    In Claude: crea l&apos;artifact → Condividi → copia il link
                    pubblico.
                </p>
            </div>

            <ArtifactBlock
                title="Argomenti del PED"
                initialPrompt={artifactPrompts.topics}
                artifactUrl={topicsArtifactUrl ?? ''}
                savingUrl={processing}
                urlError={errors.topics_artifact_url}
                onSaveUrl={(url) => save('topics_artifact_url', url)}
            />

            <ArtifactBlock
                title="Strategia shooting"
                initialPrompt={artifactPrompts.shooting}
                artifactUrl={shootingArtifactUrl ?? ''}
                savingUrl={processing}
                urlError={errors.shooting_artifact_url}
                onSaveUrl={(url) => save('shooting_artifact_url', url)}
            />
        </Card>
    );
}

export default function ClientsEdit({
    client,
    assignableUsers,
    publicLink,
    accessUsers,
    artifactPrompts,
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
                    <ArtifactPanel
                        clientId={client.id}
                        artifactPrompts={artifactPrompts}
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
