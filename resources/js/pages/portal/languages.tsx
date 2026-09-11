import { Head, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { store } from '@/routes/portal/languages';

type Language = { id: number; name: string };
type MyLanguage = Language & {
    employee_language: {
        speaking_level: string | null;
        writing_level: string | null;
        reading_level: string | null;
    } | null;
};

type PageProps = {
    languages: Language[];
    myLanguages: MyLanguage[];
};

const levels = ['beginner', 'intermediate', 'advanced', 'native'];

function humanize(value: string | null): string {
    if (!value) {
        return '—';
    }

    return value.charAt(0).toUpperCase() + value.slice(1);
}

export default function PortalLanguages() {
    const { languages, myLanguages } = usePage<PageProps>().props;

    const { data, setData, errors, processing, submit } = useForm({
        language_id: '',
        speaking_level: '',
        writing_level: '',
        reading_level: '',
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
            <Head title="My Languages" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">My Languages</h1>
                    <p className="text-sm text-muted-foreground">
                        Language changes go through HR approval before they are applied.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Request a language change</CardTitle>
                            <CardDescription>Add language proficiency levels</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor="language_id">Language</Label>
                                <Select
                                    value={data.language_id}
                                    onValueChange={(value) => setData('language_id', value)}
                                >
                                    <SelectTrigger id="language_id">
                                        <SelectValue placeholder="Choose a language" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {languages.map((language) => (
                                            <SelectItem key={language.id} value={String(language.id)}>
                                                {language.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {(['speaking_level', 'writing_level', 'reading_level'] as const).map(
                                (field) => (
                                    <div className="grid gap-2" key={field}>
                                        <Label htmlFor={field}>
                                            {field.replace('_level', '')} level
                                        </Label>
                                        <Select
                                            value={data[field]}
                                            onValueChange={(value) => setData(field, value)}
                                        >
                                            <SelectTrigger id={field}>
                                                <SelectValue placeholder="Select a level" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {levels.map((level) => (
                                                    <SelectItem key={level} value={level}>
                                                        {humanize(level)}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                ),
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="note">Note for HR (optional)</Label>
                                <textarea
                                    id="note"
                                    className="min-h-12 w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                                    value={data.note}
                                    onChange={(e) => setData('note', e.target.value)}
                                />
                            </div>

                            {errors.language_id && (
                                <p className="text-sm text-red-600">{errors.language_id}</p>
                            )}

                            <Button onClick={submitRequest} disabled={processing}>
                                {processing ? 'Submitting…' : 'Submit for approval'}
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">My current languages</CardTitle>
                            <CardDescription>As recorded in your employee profile</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {myLanguages.length === 0 && (
                                <p className="text-sm text-muted-foreground">
                                    No languages recorded yet.
                                </p>
                            )}
                            {myLanguages.map((language) => (
                                <div
                                    key={language.id}
                                    className="flex items-start justify-between gap-3 rounded-lg border p-3 text-sm"
                                >
                                    <div>
                                        <p className="font-medium">{language.name}</p>
                                        <p className="text-muted-foreground">
                                            Speaking: {humanize(language.employee_language?.speaking_level ?? null)} ·{' '}
                                            Writing: {humanize(language.employee_language?.writing_level ?? null)} ·{' '}
                                            Reading: {humanize(language.employee_language?.reading_level ?? null)}
                                        </p>
                                    </div>
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

PortalLanguages.layout = {
    breadcrumbs: [{ title: 'My Languages', href: '/portal/languages' }],
};