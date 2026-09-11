import { Head, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

type ChangeRequest = {
    id: number;
    type: string;
    subject_id: number | null;
    status: string;
    payload: Record<string, unknown>;
    previous: Record<string, unknown> | null;
    submitted_note: string | null;
    reviewer_note: string | null;
    reviewer: { id: number; name: string } | null;
    reviewed_at: string | null;
    created_at: string | null;
};

type PageProps = {
    requests: ChangeRequest[];
};

const statusColors: Record<string, string> = {
    pending: 'bg-amber-500/15 text-amber-700',
    approved: 'bg-emerald-500/15 text-emerald-700',
    rejected: 'bg-red-500/15 text-red-700',
};

function humanize(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return value.replaceAll('_', ' ');
}

function displayValue(value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (value instanceof Date) {
        return value.toDateString();
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    return String(value);
}

export default function PortalRequests() {
    const { requests } = usePage<PageProps>().props;

    return (
        <>
            <Head title="My Requests" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">My Requests</h1>
                    <p className="text-sm text-muted-foreground">
                        Every profile change you submit is listed here with its approval status.
                    </p>
                </div>

                {requests.length === 0 && (
                    <Card>
                        <CardContent className="py-10 text-center text-sm text-muted-foreground">
                            You haven’t submitted any requests yet.
                        </CardContent>
                    </Card>
                )}

                <div className="space-y-4">
                    {requests.map((request) => (
                        <Card key={request.id}>
                            <CardHeader className="flex flex-row items-start justify-between space-y-0">
                                <div>
                                    <CardTitle className="text-base">
                                        {humanize(request.type)} change
                                    </CardTitle>
                                    <p className="text-sm text-muted-foreground">
                                        Submitted {request.created_at?.replace('T', ' ').slice(0, 16)}
                                    </p>
                                </div>
                                <Badge
                                    variant="secondary"
                                    className={statusColors[request.status] ?? ''}
                                >
                                    {humanize(request.status)}
                                </Badge>
                            </CardHeader>
                            <CardContent className="space-y-4 text-sm">
                                <dl className="grid gap-x-6 gap-y-2 sm:grid-cols-2">
                                    {Object.entries(request.payload).map(([key, value]) => (
                                        <div key={key}>
                                            <dt className="text-muted-foreground">
                                                {humanize(key.replaceAll('_', ' '))}
                                            </dt>
                                            <dd className="font-medium">{displayValue(value)}</dd>
                                        </div>
                                    ))}
                                </dl>

                                {request.previous && (
                                    <div className="rounded-lg bg-muted/40 p-3">
                                        <p className="mb-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                            Previous state
                                        </p>
                                        <dl className="grid gap-x-6 gap-y-2 sm:grid-cols-2">
                                            {Object.entries(request.previous).map(([key, value]) => (
                                                <div key={key}>
                                                    <dt className="text-muted-foreground">
                                                        {humanize(key.replaceAll('_', ' '))}
                                                    </dt>
                                                    <dd className="font-medium">{displayValue(value)}</dd>
                                                </div>
                                            ))}
                                        </dl>
                                    </div>
                                )}

                                {request.submitted_note && (
                                    <p className="text-sm text-muted-foreground">
                                        Your note: {request.submitted_note}
                                    </p>
                                )}

                                {request.reviewed_at && (
                                    <div className="border-t pt-3 text-muted-foreground">
                                        Reviewed by {request.reviewer?.name ?? '—'}{' '}
                                        on {request.reviewed_at?.replace('T', ' ').slice(0, 16)}
                                        {request.reviewer_note ? ` — ${request.reviewer_note}` : ''}
                                    </div>
                                )}
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}

PortalRequests.layout = {
    breadcrumbs: [{ title: 'My Requests', href: '/portal/my-requests' }],
};