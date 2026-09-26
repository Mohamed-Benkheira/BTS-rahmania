import { Head, useForm, usePage } from '@inertiajs/react';
import InputError from '@/components/input-error';
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
import { formatDate, formatDateTime } from '@/lib/utils';
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

    const { data, setData, errors, processing, submit, reset } = useForm({
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
            onSuccess: () => reset(),
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
                        <CardContent>
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    submitRequest();
                                }}
                                className="space-y-4"
                            >
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="start_date">Start date</Label>
                                        <Input
                                            id="start_date"
                                            type="date"
                                            value={data.start_date}
                                            onChange={(e) => setData('start_date', e.target.value)}
                                        />
                                        <InputError message={errors.start_date} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="end_date">End date</Label>
                                        <Input
                                            id="end_date"
                                            type="date"
                                            min={data.start_date || undefined}
                                            value={data.end_date}
                                            onChange={(e) => setData('end_date', e.target.value)}
                                        />
                                        <InputError message={errors.end_date} />
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
                                    <InputError message={errors.availability_percentage} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="reason">Reason</Label>
                                    <textarea
                                        id="reason"
                                        className="min-h-16 w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                                        value={data.reason}
                                        onChange={(e) => setData('reason', e.target.value)}
                                        placeholder="e.g. annual leave, training, partial allocation…"
                                        maxLength={500}
                                    />
                                    <InputError message={errors.reason} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="note">Note for HR (optional)</Label>
                                    <textarea
                                        id="note"
                                        className="min-h-12 w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                                        value={data.note}
                                        onChange={(e) => setData('note', e.target.value)}
                                        placeholder="Any additional context for this request…"
                                        maxLength={1000}
                                    />
                                    <InputError message={errors.note} />
                                </div>

                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Submitting…' : 'Submit for approval'}
                                </Button>
                            </form>
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
                                                {formatDate(availability.start_date)} → {formatDate(availability.end_date)}
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
                                                Submitted {formatDateTime(request.created_at)}
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