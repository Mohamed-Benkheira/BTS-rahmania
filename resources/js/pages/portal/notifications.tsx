import { Head, router, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { readAll } from '@/routes/portal/notifications';

type NotificationItem = {
    id: string;
    data: Record<string, unknown>;
    read: boolean;
    created_at: string | null;
};

type PageProps = {
    notifications: NotificationItem[];
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
        const expires = data.expires_at ? ` expiring ${String(data.expires_at)}` : '';

        return `Certification${expires}: ${String(data.certification_name)}`;
    }

    return 'New notification';
}

function descriptionFor(data: Record<string, unknown>): string | null {
    if (data.reviewer_note) {
        return `HR note: ${String(data.reviewer_note)}`;
    }

    if (data.employee_name) {
        return `${String(data.employee_name)} · ${String(data.type)} change`;
    }

    return null;
}

export default function PortalNotifications() {
    const { notifications } = usePage<PageProps>().props;

    const markAllRead = () => {
        router.post(readAll().url, {});
    };

    return (
        <>
            <Head title="Notifications" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Notifications</h1>
                        <p className="text-sm text-muted-foreground">
                            Activity about your requests and assignments.
                        </p>
                    </div>
                    <Button variant="outline" size="sm" onClick={markAllRead}>
                        Mark all as read
                    </Button>
                </div>

                {notifications.length === 0 && (
                    <Card>
                        <CardContent className="py-10 text-center text-sm text-muted-foreground">
                            No notifications yet.
                        </CardContent>
                    </Card>
                )}

                <div className="space-y-3">
                    {notifications.map((notification) => (
                        <Card key={notification.id}>
                            <CardHeader className="flex flex-row items-start justify-between space-y-0">
                                <div>
                                    <CardTitle className="text-base">
                                        {titleFor(notification.data ?? {})}
                                    </CardTitle>
                                    {descriptionFor(notification.data ?? {}) && (
                                        <p className="text-sm text-muted-foreground">
                                            {descriptionFor(notification.data ?? {})}
                                        </p>
                                    )}
                                </div>
                                <span className="text-xs text-muted-foreground">
                                    {notification.created_at}
                                </span>
                            </CardHeader>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}

PortalNotifications.layout = {
    breadcrumbs: [{ title: 'Notifications', href: '/portal/notifications' }],
};