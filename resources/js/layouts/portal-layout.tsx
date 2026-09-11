import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { NotificationBell } from '@/components/notification-bell';
import { PortalSidebar } from '@/components/portal-sidebar';
import type { BreadcrumbItem } from '@/types';

export default function PortalLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {

    return (
        <AppShell variant="sidebar">
            <PortalSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader
                    breadcrumbs={breadcrumbs}
                    actions={<NotificationBell />}
                />
                {children}
            </AppContent>
        </AppShell>
    );
}