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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store } from '@/routes/portal/certifications';

type Certification = {
    id: number;
    name: string;
    issuer: string | null;
    description: string | null;
};
type MyCertification = { id: number; name: string; issuer: string | null } & {
    certificate: {
        certificate_number: string | null;
        issued_at: string | null;
        expires_at: string | null;
        verification_status: string | null;
        document_path: string | null;
    } | null;
};

type PageProps = {
    certifications: Certification[];
    myCertifications: MyCertification[];
};

function humanize(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return value.replaceAll('_', ' ');
}

export default function PortalCertifications() {
    const { certifications, myCertifications } = usePage<PageProps>().props;

    const { data, setData, errors, processing, submit } = useForm({
        certification_id: '',
        certificate_number: '',
        issued_at: '',
        expires_at: '',
        document: null as File | null,
        note: '',
    });

    const submitRequest = () => {
        submit('post', store().url, {
            method: 'post',
            preserveScroll: true,
            forceFormData: true,
        });
    };

    return (
        <>
            <Head title="My Certifications" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">My Certifications</h1>
                    <p className="text-sm text-muted-foreground">
                        Approval by HR verifies the certification — no second step is needed.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Request a certification</CardTitle>
                            <CardDescription>Add or update a certificate</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor="certification_id">Certification</Label>
                                <Select
                                    value={data.certification_id}
                                    onValueChange={(value) => setData('certification_id', value)}
                                >
                                    <SelectTrigger id="certification_id">
                                        <SelectValue placeholder="Choose a certification" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {certifications.map((certification) => (
                                            <SelectItem key={certification.id} value={String(certification.id)}>
                                                {certification.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="certificate_number">Certificate number</Label>
                                <Input
                                    id="certificate_number"
                                    value={data.certificate_number}
                                    onChange={(e) => setData('certificate_number', e.target.value)}
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="issued_at">Issued date</Label>
                                    <Input
                                        id="issued_at"
                                        type="date"
                                        value={data.issued_at}
                                        onChange={(e) => setData('issued_at', e.target.value)}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="expires_at">Expires date</Label>
                                    <Input
                                        id="expires_at"
                                        type="date"
                                        value={data.expires_at}
                                        onChange={(e) => setData('expires_at', e.target.value)}
                                    />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="document">Document (PDF or image)</Label>
                                <Input
                                    id="document"
                                    type="file"
                                    accept="application/pdf,image/png,image/jpeg"
                                    onChange={(e) => setData('document', e.target.files?.[0] ?? null)}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="note">Note for HR (optional)</Label>
                                <textarea
                                    id="note"
                                    className="min-h-12 w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                                    value={data.note}
                                    onChange={(e) => setData('note', e.target.value)}
                                />
                            </div>

                            {errors.certification_id && (
                                <p className="text-sm text-red-600">{errors.certification_id}</p>
                            )}

                            <Button onClick={submitRequest} disabled={processing}>
                                {processing ? 'Submitting…' : 'Submit for approval'}
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">My current certifications</CardTitle>
                            <CardDescription>As recorded in your employee profile</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {myCertifications.length === 0 && (
                                <p className="text-sm text-muted-foreground">
                                    No certifications recorded yet.
                                </p>
                            )}
                            {myCertifications.map((certification) => (
                                <div
                                    key={certification.id}
                                    className="flex items-start justify-between gap-3 rounded-lg border p-3 text-sm"
                                >
                                    <div>
                                        <p className="font-medium">{certification.name}</p>
                                        <p className="text-muted-foreground">
                                            {certification.issuer ?? '—'}
                                            {certification.certificate?.expires_at
                                                ? ` · expires ${certification.certificate.expires_at}`
                                                : ''}
                                        </p>
                                    </div>
                                    <Badge variant="secondary">
                                        {humanize(certification.certificate?.verification_status)}
                                    </Badge>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

PortalCertifications.layout = {
    breadcrumbs: [{ title: 'My Certifications', href: '/portal/certifications' }],
};