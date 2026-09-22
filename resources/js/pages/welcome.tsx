import { Head, Link, usePage } from '@inertiajs/react';
import { dashboard, login } from '@/routes';
import { register } from '@/routes';

export default function Welcome() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="ContentU PED" />
            <div className="flex min-h-screen flex-col items-center justify-center gap-6 bg-background p-6 text-foreground">
                <h1 className="text-2xl font-semibold">ContentU PED</h1>

                <nav className="flex items-center gap-4 text-sm">
                    {auth.user ? (
                        <Link
                            href={dashboard()}
                            className="rounded-md border border-border px-5 py-1.5 leading-normal hover:bg-secondary"
                        >
                            Dashboard
                        </Link>
                    ) : (
                        <>
                            <Link
                                href={login()}
                                className="rounded-md border border-transparent px-5 py-1.5 leading-normal hover:bg-secondary"
                            >
                                Accedi
                            </Link>
                            <Link
                                href={register()}
                                className="rounded-md border border-border px-5 py-1.5 leading-normal hover:bg-secondary"
                            >
                                Registrati
                            </Link>
                        </>
                    )}
                </nav>
            </div>
        </>
    );
}
