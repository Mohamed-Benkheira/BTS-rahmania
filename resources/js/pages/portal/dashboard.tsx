import { Link, Head, usePage } from '@inertiajs/react';
import {
    AlertCircle,
    CalendarClock,
    ClipboardList,
    FolderKanban,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { Employee } from '@/types';

type Assignment = {
    id: number;
    status: string;
    assignment_type: string;
    start_date: string | null;
    end_date: string | null;
    project: { id: number; name: string; status: string | null } | null;
};

type Availability = {
    id: number;
    start_date: string | null;
    end_date: string | null;
    availability_percentage: number | null;
    status: string | null;
    reason: string | null;
};

type PageProps = {
    employee: Employee;
    currentAssignment: Assignment | null;
    latestAvailability: Availability | null;
    stats: {
        skills: number;
        languages: number;
        certifications: number;
        pending_requests: number;
        unread_notifications: number;
    };
};

const statusColors: Record<string, string> = {
    pending: 'bg-amber-500/15 text-amber-700',
    approved: 'bg-sky-500/15 text-sky-700',
    active: 'bg-emerald-500/15 text-emerald-700',
    completed: 'bg-neutral-500/15 text-neutral-600',
    rejected: 'bg-red-500/15 text-red-700',
    cancelled: 'bg-neutral-500/15 text-neutral-600',
};

const availabilityColors: Record<string, string> = {
    available: 'bg-emerald-500/15 text-emerald-700',
    partially_available: 'bg-amber-500/15 text-amber-700',
    unavailable: 'bg-red-500/15 text-red-700',
    on_leave: 'bg-sky-500/15 text-sky-700',
};

function humanize(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return value.replaceAll('_', ' ');
}

const profileStatLinks = [
    { label: 'View skills', href: '/portal/skills' },
    { label: 'View languages', href: '/portal/languages' },
    { label: 'View certifications', href: '/portal/certifications' },
] as const;

export default function PortalDashboard() {
    const { employee, currentAssignment, latestAvailability, stats } =
        usePage<PageProps>().props;

    return (
        <>
            <Head title="Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            Welcome back, {employee.first_name}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {employee.employee_code}
                            {employee.position?.name ? ` · ${employee.position.name}` : ''}
                        </p>
                    </div>
                    <Button asChild>
                        <Link href="/portal/skills">Update my profile</Link>
                    </Button>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardDescription>
                                <ClipboardList className="size-4" />
                                Pending requests
                            </CardDescription>
                            <CardTitle className="text-3xl">{stats.pending_requests}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardDescription>
                                <AlertCircle className="size-4" />
                                Unread notifications
                            </CardDescription>
                            <CardTitle className="text-3xl">{stats.unread_notifications}</CardTitle>
                        </CardHeader>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardDescription>
                                <CalendarClock className="size-4" />
                                Current availability
                            </CardDescription>
                            <CardTitle className="text-2xl">
                                {latestAvailability
                                    ? `${Math.round(latestAvailability.availability_percentage ?? 0)}%`
                                    : '—'}
                            </CardTitle>
                            {latestAvailability?.status && (
                                <Badge
                                    variant="secondary"
                                    className={`mt-2 w-fit ${availabilityColors[latestAvailability.status] ?? ''}`}
                                >
                                    {humanize(latestAvailability.status)}
                                </Badge>
                            )}
                        </CardHeader>
                    </Card>
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Current assignment</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {currentAssignment ? (
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between gap-3">
                                        <div className="flex items-center gap-2">
                                            <FolderKanban className="size-4 text-muted-foreground" />
                                            <span className="font-medium">
                                                {currentAssignment.project?.name}
                                            </span>
                                        </div>
                                        <Badge
                                            variant="secondary"
                                            className={statusColors[currentAssignment.status] ?? ''}
                                        >
                                            {humanize(currentAssignment.status)}
                                        </Badge>
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        {currentAssignment.start_date} → {currentAssignment.end_date}
                                    </p>
                                </div>
                            ) : (
                                <p className="text-sm text-muted-foreground">
                                    You are not currently assigned to any project.
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Your profile</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <dl className="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                <div>
                                    <dt className="text-muted-foreground">Skills</dt>
                                    <dd className="font-medium">{stats.skills}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Languages</dt>
                                    <dd className="font-medium">{stats.languages}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Certifications</dt>
                                    <dd className="font-medium">{stats.certifications}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Status</dt>
                                    <dd className="font-medium">{humanize(employee.employment_status)}</dd>
                                </div>
                            </dl>
                            <div className="mt-4 flex flex-wrap gap-2">
                                {profileStatLinks.map(({ label, href }) => (
                                    <Button key={href} variant="outline" size="sm" asChild>
                                        <Link href={href}>{label}</Link>
                                    </Button>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

PortalDashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: '/portal' }],
};