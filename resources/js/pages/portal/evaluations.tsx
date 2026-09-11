import { Head, usePage } from '@inertiajs/react';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat('en', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(new Date(value));
}

type Evaluation = {
    id: number;
    rating: number | null;
    communication_rating: number | null;
    delivery_rating: number | null;
    quality_rating: number | null;
    comments: string | null;
    evaluated_at: string | null;
    project: { id: number; name: string } | null;
};

type PageProps = {
    evaluations: Evaluation[];
};

export default function PortalEvaluations() {
    const { evaluations } = usePage<PageProps>().props;

    return (
        <>
            <Head title="My Evaluations" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">My Evaluations</h1>
                    <p className="text-sm text-muted-foreground">
                        Project evaluations recorded against your assignments.
                    </p>
                </div>

                {evaluations.length === 0 && (
                    <Card>
                        <CardContent className="py-10 text-center text-sm text-muted-foreground">
                            No evaluations recorded yet.
                        </CardContent>
                    </Card>
                )}

                <div className="space-y-4">
                    {evaluations.map((evaluation) => (
                        <Card key={evaluation.id}>
                            <CardHeader>
                                <CardTitle className="text-base">
                                    {evaluation.project?.name ?? 'Project'}
                                </CardTitle>
                                <p className="text-sm text-muted-foreground">
                                    Evaluated {formatDate(evaluation.evaluated_at)}
                                </p>
                            </CardHeader>
                            <CardContent className="text-sm">
                                <dl className="flex flex-wrap gap-x-8 gap-y-2">
                                    <div>
                                        <dt className="text-muted-foreground">Overall</dt>
                                        <dd className="font-medium">
                                            {evaluation.rating != null ? `${evaluation.rating}/5` : '—'}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Communication</dt>
                                        <dd className="font-medium">
                                            {evaluation.communication_rating != null
                                                ? `${evaluation.communication_rating}/5`
                                                : '—'}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Delivery</dt>
                                        <dd className="font-medium">
                                            {evaluation.delivery_rating != null
                                                ? `${evaluation.delivery_rating}/5`
                                                : '—'}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Quality</dt>
                                        <dd className="font-medium">
                                            {evaluation.quality_rating != null
                                                ? `${evaluation.quality_rating}/5`
                                                : '—'}
                                        </dd>
                                    </div>
                                </dl>
                                {evaluation.comments && (
                                    <p className="mt-3 text-muted-foreground">
                                        {evaluation.comments}
                                    </p>
                                )}
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}

PortalEvaluations.layout = {
    breadcrumbs: [{ title: 'My Evaluations', href: '/portal/my-evaluations' }],
};