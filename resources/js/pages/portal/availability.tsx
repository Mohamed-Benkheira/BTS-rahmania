import { Head, useForm, usePage } from '@inertiajs/react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { store } from '@/routes/portal/availability';

type Availability = {
    id: number;
    start_date: string | null;
    end_date: string | null;
    availability_percentage: number | null;
    status: string | null;
    reason: string | null;
};

type PageProps = {
    availabilities: Availability[];
    pendingRequests: { id: number; status: string; created_at: string | null }[];
};

const statusColors: Record<string, string> = {
    available: 'bg-emerald-500/15 text-emerald-700',
    partially_available: 'bg-amber-500/15 text-amber-700',
    unavailable: 'bg-red-500/15 text-red-700',
    on_leave: 'bg-sky-500/15 text-sky-700',
};

function humanize(value: string | null): string {
    if (!value) {
        return '—';
    }

    return value.replaceAll('_', ' ');
}

export default function PortalAvailability() {
    const { availabilities, pendingRequests } = usePage<PageProps>().props;

    const { data, setData, errors, processing, submit } = useForm({
        start_date: '',
        end_date: '',
        availability_percentage: '',
        reason: '',
        note: '',
    });

    const submitRequest = () => {
        submit('post', store().url, {
            method: 'post',
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title="My Availability" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">My Availability</h1>
                    <p className="text-sm text-muted-foreground">
                        The latest approved availability wins — overlapping ranges are simply replaced.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Request an availability change</CardTitle>
                            <CardDescription>Leave, or a change in your allocation</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="start_date">Start date</Label>
                                    <Input
                                        id="start_date"
                                        type="date"
                                        value={data.start_date}
                                        onChange={(e) => setData('start_date', e.target.value)}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="end_date">End date</Label>
                                    <Input
                                        id="end_date"
                                        type="date"
                                        value={data.end_date}
                                        onChange={(e) => setData('end_date', e.target.value)}
                                    />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="availability_percentage">
                                    Availability (0–100%)
                                </Label>
                                <Input
                                    id="availability_percentage"
                                    type="number"
                                    min={0}
                                    max={100}
                                    value={data.availability_percentage}
                                    onChange={(e) => setData('availability_percentage', e.target.value)}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="reason">Reason</Label>
                                <textarea
                                    id="reason"
                                    className="min-h-16 w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                                    value={data.reason}
                                    onChange={(e) => setData('reason', e.target.value)}
                                    placeholder="e.g. annual leave, training, partial allocation…"
                                />
                            </div>

                            {(errors.start_date || errors.end_date || errors.availability_percentage) && (
                                <p className="text-sm text-red-600">
                                    {errors.start_date ?? errors.end_date ?? errors.availability_percentage}
                                </p>
                            )}

                            <Button onClick={submitRequest} disabled={processing}>
                                {processing ? 'Submitting…' : 'Submit for approval'}
                            </Button>
                        </CardContent>
                    </Card>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">Availability history</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                {availabilities.length === 0 && (
                                    <p className="text-sm text-muted-foreground">
                                        No availability recorded yet.
                                    </p>
                                )}
                                {availabilities.map((availability) => (
                                    <div
                                        key={availability.id}
                                        className="flex items-start justify-between gap-3 rounded-lg border p-3 text-sm"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {availability.start_date} → {availability.end_date}
                                            </p>
                                            <p className="text-muted-foreground">
                                                {Math.round(availability.availability_percentage ?? 0)}%{availability.reason ? ` · ${availability.reason}` : ''}
                                            </p>
                                        </div>
                                        <Badge
                                            variant="secondary"
                                            className={statusColors[availability.status ?? ''] ?? ''}
                                        >
                                            {humanize(availability.status)}
                                        </Badge>
                                    </div>
                                ))}
                            </CardContent>
                        </Card>

                        {pendingRequests.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base">Pending requests</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2">
                                    {pendingRequests.map((request) => (
                                        <div
                                            key={request.id}
                                            className="flex items-center justify-between text-sm"
                                        >
                                            <span className="text-muted-foreground">
                                                Submitted {request.created_at?.replace('T', ' ').slice(0, 16)}
                                            </span>
                                            <Badge variant="secondary">Pending</Badge>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

PortalAvailability.layout = {
    breadcrumbs: [{ title: 'My Availability', href: '/portal/availability' }],
};