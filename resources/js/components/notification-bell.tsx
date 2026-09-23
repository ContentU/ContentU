import { Deferred, Link, router, usePage } from '@inertiajs/react';
import { Bell } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { cn } from '@/lib/utils';

function NotificationList() {
    const { notifications } = usePage().props;
    const items = notifications?.items ?? [];

    const markAllRead = () => {
        router.patch('/notifications/read-all');
    };

    const markRead = (id: string) => {
        router.patch(`/notifications/${id}/read`);
    };

    return (
        <div className="mt-4 space-y-4">
            {items.length > 0 && (
                <Button variant="outline" size="sm" onClick={markAllRead}>
                    Segna tutte come lette
                </Button>
            )}

            <ul className="space-y-2">
                {items.map((item) => (
                    <li
                        key={item.id}
                        className={cn(
                            'rounded-md p-3 text-sm',
                            !item.readAt && 'bg-accent',
                        )}
                    >
                        <Link
                            href={item.url ?? '#'}
                            onClick={() => markRead(item.id)}
                            className="block"
                        >
                            {item.client && (
                                <p className="text-xs font-medium text-muted-foreground">
                                    {item.client}
                                </p>
                            )}
                            <p className="font-medium">{item.title}</p>
                            <p className="text-muted-foreground">
                                {item.message}
                            </p>
                            <p className="mt-1 text-xs text-muted-foreground">
                                {item.at}
                            </p>
                        </Link>
                    </li>
                ))}

                {items.length === 0 && (
                    <p className="text-sm text-muted-foreground">
                        Nessuna notifica.
                    </p>
                )}
            </ul>
        </div>
    );
}

export function NotificationBell() {
    const { notifications } = usePage().props;

    return (
        <Sheet>
            <SheetTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    aria-label="Notifiche"
                    className="relative"
                >
                    <Bell className="size-5" />
                    {!!notifications?.unreadCount && (
                        <span className="absolute -right-1 -top-1 flex size-4 items-center justify-center rounded-full bg-destructive font-mono text-[10px] text-destructive-foreground">
                            {notifications.unreadCount}
                        </span>
                    )}
                </Button>
            </SheetTrigger>

            <SheetContent side="right" className="w-96">
                <SheetHeader>
                    <SheetTitle>Notifiche</SheetTitle>
                </SheetHeader>
                <Deferred
                    data="notifications"
                    fallback={
                        <p className="p-4 text-muted-foreground">
                            Caricamento…
                        </p>
                    }
                >
                    <NotificationList />
                </Deferred>
            </SheetContent>
        </Sheet>
    );
}
