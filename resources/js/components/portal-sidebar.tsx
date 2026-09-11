import { Link } from '@inertiajs/react';
import {
    Award,
    Bell,
    CalendarClock,
    ClipboardList,
    FolderKanban,
    Languages,
    LayoutGrid,
    Sparkles,
    Star,
    UserRound,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/portal';
import type { NavItem } from '@/types';

const portalNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/portal', icon: LayoutGrid },
    { title: 'My Profile', href: '/portal/profile', icon: UserRound },
    { title: 'My Skills', href: '/portal/skills', icon: Sparkles },
    { title: 'My Languages', href: '/portal/languages', icon: Languages },
    { title: 'My Certifications', href: '/portal/certifications', icon: Award },
    { title: 'My Availability', href: '/portal/availability', icon: CalendarClock },
    { title: 'My Requests', href: '/portal/my-requests', icon: ClipboardList },
    { title: 'My Assignments', href: '/portal/my-assignments', icon: FolderKanban },
    { title: 'My Evaluations', href: '/portal/my-evaluations', icon: Star },
    { title: 'Notifications', href: '/portal/notifications', icon: Bell },
];

export function PortalSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard().url} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={portalNavItems} groupLabel="Employee Portal" />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}