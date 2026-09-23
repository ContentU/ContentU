import { router, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Role = 'admin' | 'account_manager' | 'copywriter';

type UserRow = {
    id: number;
    name: string;
    email: string;
    role: Role;
    isActive: boolean;
    clients: string[];
};

type Props = {
    users: UserRow[];
    roles: { value: Role; label: string }[];
};

export default function UsersSettings({ users, roles }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        role: 'copywriter' as Role,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/settings/users', { onSuccess: () => reset() });
    };

    const changeRole = (user: UserRow, role: Role) => {
        router.patch(`/settings/users/${user.id}`, { name: user.name, role });
    };

    const toggleActive = (user: UserRow) => {
        router.patch(`/settings/users/${user.id}/toggle-active`);
    };

    return (
        <>
            <h1 className="sr-only">Utenti del team</h1>

            <div className="space-y-6">
                <Heading
                    variant="small"
                    title="Utenti del team"
                    description="Invita, riassegna il ruolo o disattiva un utente interno. La cronologia di chi ha creato o approvato cosa resta sempre visibile: nessun utente viene mai cancellato."
                />

                <Card className="divide-y divide-border">
                    {users.map((user) => (
                        <div key={user.id} className="space-y-3 p-4">
                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <div className="min-w-0">
                                    <p className="font-medium">{user.name}</p>
                                    <p className="text-sm text-muted-foreground">
                                        {user.email}
                                    </p>
                                </div>
                                <Badge
                                    variant="outline"
                                    className={
                                        user.isActive
                                            ? 'bg-status-approved-bg text-status-approved'
                                            : 'bg-status-draft-bg text-status-draft'
                                    }
                                >
                                    {user.isActive ? 'Attivo' : 'Disattivato'}
                                </Badge>
                            </div>

                            <p className="text-sm text-muted-foreground">
                                Clienti assegnati:{' '}
                                {user.clients.length > 0
                                    ? user.clients.join(', ')
                                    : '—'}
                            </p>

                            <div className="flex flex-wrap items-center gap-3">
                                <Select
                                    value={user.role}
                                    onValueChange={(v) =>
                                        changeRole(user, v as Role)
                                    }
                                >
                                    <SelectTrigger className="w-44">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {roles.map((r) => (
                                            <SelectItem
                                                key={r.value}
                                                value={r.value}
                                            >
                                                {r.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>

                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => toggleActive(user)}
                                >
                                    {user.isActive ? 'Disattiva' : 'Riattiva'}
                                </Button>
                            </div>
                        </div>
                    ))}

                    {users.length === 0 && (
                        <p className="p-4 text-sm text-muted-foreground">
                            Nessun utente del team.
                        </p>
                    )}
                </Card>

                <form
                    onSubmit={submit}
                    className="flex max-w-lg flex-wrap items-end gap-3"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="name">Nome</Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        {errors.name && (
                            <p className="text-sm text-destructive">
                                {errors.name}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        {errors.email && (
                            <p className="text-sm text-destructive">
                                {errors.email}
                            </p>
                        )}
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="role">Ruolo</Label>
                        <Select
                            value={data.role}
                            onValueChange={(v) => setData('role', v as Role)}
                        >
                            <SelectTrigger id="role" className="w-44">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {roles.map((r) => (
                                    <SelectItem key={r.value} value={r.value}>
                                        {r.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <Button type="submit" disabled={processing}>
                        Invita utente
                    </Button>
                </form>
            </div>
        </>
    );
}
