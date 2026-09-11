import { Link, Head, useForm, usePage } from '@inertiajs/react';
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
import { store } from '@/routes/portal/profile';
import type { Employee } from '@/types';

type PageProps = {
    employee: Employee;
    pendingRequests: { id: number; status: string; created_at: string | null }[];
};

export default function PortalProfile() {
    const { employee, pendingRequests } = usePage<PageProps>().props;

    const { data, setData, errors, processing, submit } = useForm({
        phone: '',
        biography: '',
        birth_date: '',
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
            <Head title="My Profile" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">My Profile</h1>
                    <p className="text-sm text-muted-foreground">
                        Changes below are submitted to Human Resources for approval.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Current details</CardTitle>
                            <CardDescription>Managed and verified by HR</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <dl className="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                <div>
                                    <dt className="text-muted-foreground">Full name</dt>
                                    <dd className="font-medium">{employee.full_name}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Employee code</dt>
                                    <dd className="font-medium">{employee.employee_code}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Phone</dt>
                                    <dd className="font-medium">{employee.phone ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Birth date</dt>
                                    <dd className="font-medium">{employee.birth_date ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Position</dt>
                                    <dd className="font-medium">{employee.position?.name ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Department</dt>
                                    <dd className="font-medium">{employee.department?.name ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Team</dt>
                                    <dd className="font-medium">{employee.team?.name ?? '—'}</dd>
                                </div>
                                <div>
                                    <dt className="text-muted-foreground">Manager</dt>
                                    <dd className="font-medium">{employee.manager?.full_name ?? '—'}</dd>
                                </div>
                                <div className="col-span-2">
                                    <dt className="text-muted-foreground">Biography</dt>
                                    <dd className="mt-1 whitespace-pre-wrap">{employee.biography ?? '—'}</dd>
                                </div>
                            </dl>
                        </CardContent>
                    </Card>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">Request a change</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="phone">Phone</Label>
                                    <Input
                                        id="phone"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        placeholder="+213 770 00 00 00"
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="birth_date">Birth date</Label>
                                    <Input
                                        id="birth_date"
                                        type="date"
                                        value={data.birth_date}
                                        onChange={(e) => setData('birth_date', e.target.value)}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="biography">Biography</Label>
                                    <textarea
                                        id="biography"
                                        className="min-h-24 w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                                        value={data.biography}
                                        onChange={(e) => setData('biography', e.target.value)}
                                        placeholder="A short professional summary…"
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="note">Note for HR (optional)</Label>
                                    <textarea
                                        id="note"
                                        className="min-h-16 w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                                        value={data.note}
                                        onChange={(e) => setData('note', e.target.value)}
                                    />
                                </div>

                                {(errors.phone || errors.birth_date || errors.biography || errors.note) && (
                                    <p className="text-sm text-red-600">
                                        {errors.phone ?? errors.birth_date ?? errors.biography ?? errors.note}
                                    </p>
                                )}

                                <Button onClick={submitRequest} disabled={processing}>
                                    {processing ? 'Submitting…' : 'Submit for approval'}
                                </Button>
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
                                    <Button variant="link" size="sm" className="h-auto px-0" asChild>
                                        <Link href="/portal/my-requests">View all requests</Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

PortalProfile.layout = {
    breadcrumbs: [{ title: 'My Profile', href: '/portal/profile' }],
};