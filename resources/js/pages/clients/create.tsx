import { Head } from '@inertiajs/react';
import { ClientForm, type AssignableUser } from '@/pages/clients/form';

type Props = {
    assignableUsers: AssignableUser[];
};

export default function ClientsCreate({ assignableUsers }: Props) {
    return (
        <>
            <Head title="Nuovo cliente" />

            <div className="p-4 md:p-6">
                <h1 className="font-serif text-2xl">Nuovo cliente</h1>

                <div className="mt-6">
                    <ClientForm assignableUsers={assignableUsers} />
                </div>
            </div>
        </>
    );
}
