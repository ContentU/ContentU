import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import {
    ClientBrandMark,
    type PublicClient,
} from '@/components/public-ped/client-brand';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    client: PublicClient;
    clientSlug: string;
    mode: 'login' | 'not_allowed' | 'pending_invite' | null;
    email: string | null;
};

function ClientHeader({ client }: { client: PublicClient }) {
    return (
        <div className="mb-8 flex flex-col items-center gap-3 text-center">
            <ClientBrandMark client={client} size="lg" />
            <div>
                <p className="text-sm text-muted-foreground">
                    Piano editoriale
                </p>
                <h1 className="font-serif text-2xl">{client.name}</h1>
            </div>
        </div>
    );
}

function EmailStep({
    client,
    clientSlug,
}: {
    client: PublicClient;
    clientSlug: string;
}) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/ped/${clientSlug}/check-email`);
    };

    return (
        <>
            <ClientHeader client={client} />
            <form
                onSubmit={submit}
                className="mx-auto grid w-full max-w-sm gap-4"
            >
                <div className="grid gap-2">
                    <Label htmlFor="email">Il tuo indirizzo email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        autoFocus
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    <InputError message={errors.email} />
                </div>
                <Button type="submit" disabled={processing}>
                    {processing && <Spinner />}
                    Continua
                </Button>
            </form>
        </>
    );
}

function LoginStep({ client, email }: { client: PublicClient; email: string }) {
    const { data, setData, post, processing, errors } = useForm({
        email,
        password: '',
        remember: false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <>
            <ClientHeader client={client} />
            <form
                onSubmit={submit}
                className="mx-auto grid w-full max-w-sm gap-4"
            >
                <p className="text-center text-sm text-muted-foreground">
                    Bentornato/a, {email}
                </p>
                <div className="grid gap-2">
                    <Label htmlFor="password">Password</Label>
                    <PasswordInput
                        id="password"
                        required
                        autoFocus
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    <InputError message={errors.password} />
                </div>
                <Button type="submit" disabled={processing}>
                    {processing && <Spinner />}
                    Accedi
                </Button>
            </form>
        </>
    );
}

function BackToEmailLink({ clientSlug }: { clientSlug: string }) {
    return (
        <a
            href={`/ped/${clientSlug}`}
            className="text-center text-sm text-muted-foreground underline underline-offset-2 hover:text-foreground"
        >
            Riprova con un&apos;altra email
        </a>
    );
}

function NotAllowedStep({
    client,
    clientSlug,
}: {
    client: PublicClient;
    clientSlug: string;
}) {
    return (
        <>
            <ClientHeader client={client} />
            <div className="mx-auto grid w-full max-w-sm gap-4 text-center">
                <p className="text-sm text-muted-foreground">
                    Questa email non è autorizzata ad accedere al PED di{' '}
                    {client.name}. Contatta l&apos;agenzia per richiedere
                    l&apos;accesso.
                </p>
                <BackToEmailLink clientSlug={clientSlug} />
            </div>
        </>
    );
}

function PendingInviteStep({
    client,
    clientSlug,
    email,
}: {
    client: PublicClient;
    clientSlug: string;
    email: string;
}) {
    return (
        <>
            <ClientHeader client={client} />
            <div className="mx-auto grid w-full max-w-sm gap-4 text-center">
                <p className="text-sm text-muted-foreground">
                    Ti abbiamo inviato un&apos;email a {email} con il link per
                    impostare la password. Controlla anche lo spam.
                </p>
                <BackToEmailLink clientSlug={clientSlug} />
            </div>
        </>
    );
}

export default function PublicPedEntry({
    client,
    clientSlug,
    mode,
    email,
}: Props) {
    return (
        <div className="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-12">
            <Head title={`${client.name} — Accesso PED`} />

            {mode === null && (
                <EmailStep client={client} clientSlug={clientSlug} />
            )}
            {mode === 'login' && email && (
                <LoginStep client={client} email={email} />
            )}
            {mode === 'not_allowed' && (
                <NotAllowedStep client={client} clientSlug={clientSlug} />
            )}
            {mode === 'pending_invite' && email && (
                <PendingInviteStep
                    client={client}
                    clientSlug={clientSlug}
                    email={email}
                />
            )}
        </div>
    );
}
