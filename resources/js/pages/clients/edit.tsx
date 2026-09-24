import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
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
        router.post(`/clients/${clientId}/access/${userId}/resend`, {}, { preserveScroll: true });
    };

    const remove = (userId: number) => {
        router.delete(`/clients/${clientId}/access/${userId}`, { preserveScroll: true });
    };

    return (
        <Card className="max-w-2xl space-y-4 p-4">
            <div>
                <h2 className="font-medium">Accesso al PED</h2>
                <p className="text-sm text-muted-foreground">
                    Solo le email autorizzate qui sotto possono accedere al PED di questo cliente.
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
                                    {user.hasSetPassword ? 'Attivo' : 'Invito inviato'}
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
                    <ClientAccessPanel
                        clientId={client.id}
                        accessUsers={accessUsers}
                    />
                </div>
            </div>
        </>
    );
}
