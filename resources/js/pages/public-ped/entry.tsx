import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type PublicClient = {
    name: string;
    initials: string;
    logoUrl: string | null;
};

type Props = {
    client: PublicClient;
    token: string;
    mode: 'login' | 'register' | null;
    email: string | null;
    passwordRules: string;
};

function ClientHeader({ client }: { client: PublicClient }) {
    return (
        <div className="mb-8 flex flex-col items-center gap-3 text-center">
            {client.logoUrl ? (
                <img
                    src={client.logoUrl}
                    alt={client.name}
                    className="size-16 rounded-md object-contain"
                />
            ) : (
                <div className="flex size-16 items-center justify-center rounded-md bg-muted font-serif text-xl">
                    {client.initials}
                </div>
            )}
            <div>
                <p className="text-sm text-muted-foreground">
                    Piano editoriale
                </p>
                <h1 className="font-serif text-2xl">{client.name}</h1>
            </div>
        </div>
    );
}

function EmailStep({ client, token }: { client: PublicClient; token: string }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/ped/${token}/check-email`);
    };

    return (
        <>
            <ClientHeader client={client} />
            <form onSubmit={submit} className="mx-auto grid w-full max-w-sm gap-4">
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

function LoginStep({
    client,
    email,
}: {
    client: PublicClient;
    email: string;
}) {
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
            <form onSubmit={submit} className="mx-auto grid w-full max-w-sm gap-4">
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

function RegisterStep({
    client,
    email,
    passwordRules,
}: {
    client: PublicClient;
    email: string;
    passwordRules: string;
}) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email,
        password: '',
        password_confirmation: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/register');
    };

    return (
        <>
            <ClientHeader client={client} />
            <form onSubmit={submit} className="mx-auto grid w-full max-w-sm gap-4">
                <p className="text-center text-sm text-muted-foreground">
                    Prima volta qui, {email}? Crea il tuo accesso.
                </p>
                <div className="grid gap-2">
                    <Label htmlFor="name">Nome</Label>
                    <Input
                        id="name"
                        required
                        autoFocus
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    <InputError message={errors.name} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="password">Password</Label>
                    <PasswordInput
                        id="password"
                        required
                        passwordrules={passwordRules}
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    <InputError message={errors.password} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="password_confirmation">
                        Conferma password
                    </Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        passwordrules={passwordRules}
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                    />
                    <InputError message={errors.password_confirmation} />
                </div>
                <Button type="submit" disabled={processing}>
                    {processing && <Spinner />}
                    Crea accesso
                </Button>
            </form>
        </>
    );
}

export default function PublicPedEntry({
    client,
    token,
    mode,
    email,
    passwordRules,
}: Props) {
    return (
        <div className="mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-12">
            <Head title={`${client.name} — Accesso PED`} />

            {mode === null && <EmailStep client={client} token={token} />}
            {mode === 'login' && email && (
                <LoginStep client={client} email={email} />
            )}
            {mode === 'register' && email && (
                <RegisterStep
                    client={client}
                    email={email}
                    passwordRules={passwordRules}
                />
            )}
        </div>
    );
}
