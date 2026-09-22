import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import {
    ClientForm,
    type AssignableUser,
    type EditableClient,
} from '@/pages/clients/form';

type Props = {
    client: EditableClient;
    assignableUsers: AssignableUser[];
};

export default function ClientsEdit({ client, assignableUsers }: Props) {
    return (
        <AppLayout>
            <Head title={`Modifica ${client.name}`} />

            <h1 className="font-serif text-2xl">Modifica {client.name}</h1>

            <div className="mt-6">
                <ClientForm client={client} assignableUsers={assignableUsers} />
            </div>
        </AppLayout>
    );
}
