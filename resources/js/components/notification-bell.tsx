import { Link, router, usePage } from '@inertiajs/react';
import { Bell, CheckCheck } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { readAll } from '@/routes/portal/notifications';

type NotificationItem = {
    id: string;
    data: Record<string, unknown>;
    read: boolean;
    created_at: string | null;
};

function titleFor(data: Record<string, unknown>): string {
    const projectName = data.project_name;
    const toStatus = data.to_status;
    const type = data.type;

    if (projectName) {
        const prefix = toStatus ? `Assignment ${toStatus}` : 'Assignment created';

        return `${prefix}: ${String(projectName)}`;
    }

    if (type) {
        const status = data.status ? ` ${String(data.status)}` : '';

        return `${String(type).charAt(0).toUpperCase() + String(type).slice(1)} change request${status}`;
    }

    if (data.certification_name) {
        return `Certification expiring: ${String(data.certification_name)}`;
    }

    return 'New notification';
}

export function NotificationBell() {
    const { notifications } = usePage().props;
    const [unreadCount, setUnreadCount] = useState(notifications.unread_count ?? 0);
    const [items, setItems] = useState<NotificationItem[]>([]);

    const refresh = async () => {
        try {
            const [countRes, listRes] = await Promise.all([
                fetch('/portal/notifications/unread-count'),
                fetch('/portal/notifications/recent'),
            ]);
            const count = await countRes.json();
            const list = await listRes.json();
            setUnreadCount(count.count ?? 0);
            setItems(list.notifications ?? []);
        } catch {
            // ignore transient failures while polling
        }
    };

    useEffect(() => {
        const interval = window.setInterval(refresh, 30000);

        return () => window.clearInterval(interval);
    }, []);

    const handleOpenChange = (open: boolean) => {
        if (open) {
            refresh();
        }
    };

    const handleMarkAllRead = () => {
        router.post(readAll().url, {}, {
            onSuccess: () => {
                setItems((prev) => prev.map((item) => ({ ...item, read: true })));
                setUnreadCount(0);
            },
        });
    };

    return (
        <DropdownMenu onOpenChange={handleOpenChange}>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" aria-label="Notifications" className="relative">
                    <Bell className="size-5" />
                    {unreadCount > 0 && (
                        <span className="absolute right-1 top-1 flex size-4 items-center justify-center rounded-full bg-destructive text-[10px] font-semibold text-destructive-foreground">
                            {unreadCount > 9 ? '9+' : unreadCount}
                        </span>
                    )}
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80">
                <div className="flex items-center justify-between border-b px-3 py-2">
                    <span className="text-sm font-medium">Notifications</span>
                    {unreadCount > 0 && (
                        <Button
                            variant="ghost"
                            size="sm"
                            className="h-7 gap-1 text-xs"
                            onClick={handleMarkAllRead}
                        >
                            <CheckCheck className="size-3.5" />
                            Mark all read
                        </Button>
                    )}
                </div>
                <div className="max-h-80 overflow-y-auto py-1">
                    {items.length === 0 && (
                        <p className="px-3 py-6 text-center text-sm text-muted-foreground">
                            You’re all caught up.
                        </p>
                    )}
                    {items.map((item) => (
                        <div
                            key={item.id}
                            className={`flex flex-col gap-0.5 px-3 py-2 text-sm ${item.read ? '' : 'bg-muted/40'}`}
                        >
                            <span className={item.read ? 'font-normal' : 'font-medium'}>
                                {titleFor(item.data ?? {})}
                            </span>
                            <span className="text-xs text-muted-foreground">{item.created_at}</span>
                        </div>
                    ))}
                </div>
                <div className="border-t px-3 py-2">
                    <Button variant="link" size="sm" className="h-auto px-0 text-xs" asChild>
                        <Link href="/portal/notifications">View all</Link>
                    </Button>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}