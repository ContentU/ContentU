import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import {
    ClientForm,
    type AssignableUser,
    type EditableClient,
} from '@/pages/clients/form';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';

type PublicLink = {
    id: number;
    url: string;
    createdAt: string;
};

type Props = {
    client: EditableClient;
    assignableUsers: AssignableUser[];
    publicLink: PublicLink | null;
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

export default function ClientsEdit({
    client,
    assignableUsers,
    publicLink,
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
            </div>
        </>
    );
}
