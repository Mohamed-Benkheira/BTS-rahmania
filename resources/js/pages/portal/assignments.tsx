import { Head, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type Assignment = {
    id: number;
    status: string;
    assignment_type: string;
    start_date: string | null;
    end_date: string | null;
    assigned_at: string | null;
    project: { id: number; name: string; status: string | null } | null;
    responsibility: string | null;
    allocation_percentage: number | null;
};

type PageProps = {
    assignments: Assignment[];
};

const statusColors: Record<string, string> = {
    pending: 'bg-amber-500/15 text-amber-700',
    approved: 'bg-sky-500/15 text-sky-700',
    active: 'bg-emerald-500/15 text-emerald-700',
    completed: 'bg-neutral-500/15 text-neutral-600',
    rejected: 'bg-red-500/15 text-red-700',
    cancelled: 'bg-neutral-500/15 text-neutral-600',
};

function humanize(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return value.replaceAll('_', ' ');
}

export default function PortalAssignments() {
    const { assignments } = usePage<PageProps>().props;

    return (
        <>
            <Head title="My Assignments" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">My Assignments</h1>
                    <p className="text-sm text-muted-foreground">
                        Projects you have been assigned to, with their current status.
                    </p>
                </div>

                {assignments.length === 0 && (
                    <Card>
                        <CardContent className="py-10 text-center text-sm text-muted-foreground">
                            You haven’t been assigned to any project yet.
                        </CardContent>
                    </Card>
                )}

                <div className="space-y-4">
                    {assignments.map((assignment) => (
                        <Card key={assignment.id}>
                            <CardHeader className="flex flex-row items-start justify-between space-y-0">
                                <div>
                                    <CardTitle className="text-base">
                                        {assignment.project?.name ?? 'Assignment'}
                                    </CardTitle>
                                    <p className="text-sm text-muted-foreground">
                                        {humanize(assignment.assignment_type)} assignment ·{' '}
                                        {assignment.start_date} → {assignment.end_date}
                                    </p>
                                </div>
                                <Badge
                                    variant="secondary"
                                    className={statusColors[assignment.status] ?? ''}
                                >
                                    {humanize(assignment.status)}
                                </Badge>
                            </CardHeader>
                            <CardContent className="text-sm">
                                <dl className="flex flex-wrap gap-x-8 gap-y-2">
                                    <div>
                                        <dt className="text-muted-foreground">Allocation</dt>
                                        <dd className="font-medium">
                                            {assignment.allocation_percentage != null
                                                ? `${assignment.allocation_percentage}%`
                                                : '—'}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Responsibility</dt>
                                        <dd className="font-medium">{assignment.responsibility ?? '—'}</dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}

PortalAssignments.layout = {
    breadcrumbs: [{ title: 'My Assignments', href: '/portal/my-assignments' }],
};