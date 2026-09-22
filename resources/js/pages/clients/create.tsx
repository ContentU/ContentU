import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { ClientForm, type AssignableUser } from '@/pages/clients/form';

type Props = {
    assignableUsers: AssignableUser[];
};

export default function ClientsCreate({ assignableUsers }: Props) {
    return (
        <AppLayout>
            <Head title="Nuovo cliente" />

            <h1 className="font-serif text-2xl">Nuovo cliente</h1>

            <div className="mt-6">
                <ClientForm assignableUsers={assignableUsers} />
            </div>
        </AppLayout>
    );
}
