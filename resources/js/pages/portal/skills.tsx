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
import { store } from '@/routes/portal/skills';

type Skill = { id: number; name: string; slug: string; description: string | null };
type MySkill = Skill & {
    employee_skill: {
        proficiency_level: number | null;
        years_experience: number | null;
        last_used_at: string | null;
        notes: string | null;
    } | null;
};

type PageProps = {
    skills: Skill[];
    mySkills: MySkill[];
};

const proficiencyLabels = ['', 'Beginner', 'Basic', 'Intermediate', 'Advanced', 'Expert'];

export default function PortalSkills() {
    const { skills, mySkills } = usePage<PageProps>().props;

    const { data, setData, errors, processing, submit } = useForm({
        skill_id: '',
        proficiency_level: '',
        years_experience: '',
        notes: '',
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
            <Head title="My Skills" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">My Skills</h1>
                    <p className="text-sm text-muted-foreground">
                        Skill changes go through HR approval before they are applied.
                    </p>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Request a skill change</CardTitle>
                            <CardDescription>Add or update a skill</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor="skill_id">Skill</Label>
                                <Select
                                    value={data.skill_id}
                                    onValueChange={(value) => setData('skill_id', value)}
                                >
                                    <SelectTrigger id="skill_id">
                                        <SelectValue placeholder="Choose a skill" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {skills.map((skill) => (
                                            <SelectItem key={skill.id} value={String(skill.id)}>
                                                {skill.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="proficiency_level">Proficiency (1–5)</Label>
                                <Select
                                    value={data.proficiency_level}
                                    onValueChange={(value) => setData('proficiency_level', value)}
                                >
                                    <SelectTrigger id="proficiency_level">
                                        <SelectValue placeholder="Select a level" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {proficiencyLabels.map((label, index) =>
                                            index === 0 ? null : (
                                                <SelectItem key={index} value={String(index)}>
                                                    {index} — {label}
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectContent>
                                </Select>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="years_experience">Years of experience</Label>
                                <Input
                                    id="years_experience"
                                    type="number"
                                    min={0}
                                    step={0.5}
                                    value={data.years_experience}
                                    onChange={(e) => setData('years_experience', e.target.value)}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="notes">Notes</Label>
                                <textarea
                                    id="notes"
                                    className="min-h-16 w-full rounded-md border bg-transparent px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-ring"
                                    value={data.notes}
                                    onChange={(e) => setData('notes', e.target.value)}
                                    placeholder="Where did you use this skill?"
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

                            {(errors.skill_id || errors.proficiency_level) && (
                                <p className="text-sm text-red-600">
                                    {errors.skill_id ?? errors.proficiency_level}
                                </p>
                            )}

                            <Button onClick={submitRequest} disabled={processing}>
                                {processing ? 'Submitting…' : 'Submit for approval'}
                            </Button>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">My current skills</CardTitle>
                            <CardDescription>As recorded in your employee profile</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            {mySkills.length === 0 && (
                                <p className="text-sm text-muted-foreground">
                                    No skills recorded yet.
                                </p>
                            )}
                            {mySkills.map((skill) => (
                                <div
                                    key={skill.id}
                                    className="flex items-start justify-between gap-3 rounded-lg border p-3 text-sm"
                                >
                                    <div>
                                        <p className="font-medium">{skill.name}</p>
                                        <p className="text-muted-foreground">
                                            {skill.employee_skill
                                                ? `${proficiencyLabels[skill.employee_skill.proficiency_level ?? 0] ?? '—'}${skill.employee_skill.years_experience ? ` · ${skill.employee_skill.years_experience}y` : ''}`
                                                : 'Not recorded'}
                                        </p>
                                    </div>
                                    {skill.employee_skill?.proficiency_level ? (
                                        <Badge variant="secondary">
                                            Level {skill.employee_skill.proficiency_level}
                                        </Badge>
                                    ) : null}
                                </div>
                            ))}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

PortalSkills.layout = {
    breadcrumbs: [{ title: 'My Skills', href: '/portal/skills' }],
};