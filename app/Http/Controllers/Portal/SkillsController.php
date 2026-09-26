<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ProfileChangeType;
use App\Http\Controllers\Controller;
use App\Models\Skill;
use App\Services\ProfileChangeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkillsController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;
        $employee->loadMissing('skills');

        $mySkills = $employee->skills->map(function ($skill) {
            return [
                ...$skill->only(['id', 'name', 'slug', 'description']),
                'employee_skill' => $skill->pivot?->only(['proficiency_level', 'years_experience', 'last_used_at', 'notes']),
            ];
        })->values();

        return Inertia::render('portal/skills', [
            'skills' => Skill::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug', 'description', 'skill_category_id']),
            'mySkills' => $mySkills,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'skill_id' => ['required', 'integer', 'exists:skills,id'],
            'proficiency_level' => ['required', 'integer', 'between:1,5'],
            'years_experience' => ['nullable', 'numeric', 'min:0', 'max:60'],
            'last_used_at' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'skill_id.required' => 'Please select a skill.',
            'skill_id.exists' => 'The selected skill is invalid.',
            'proficiency_level.required' => 'Please select a proficiency level.',
            'proficiency_level.between' => 'Proficiency level must be between 1 and 5.',
            'years_experience.min' => 'Years of experience cannot be negative.',
            'years_experience.max' => 'Years of experience cannot exceed 60.',
            'last_used_at.before_or_equal' => 'Last used date cannot be in the future.',
        ]);

        $payload = [
            'proficiency_level' => (int) $validated['proficiency_level'],
            'years_experience' => isset($validated['years_experience']) && $validated['years_experience'] !== '' ? (float) $validated['years_experience'] : null,
            'last_used_at' => $validated['last_used_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        app(ProfileChangeRequestService::class)->submit(
            employee: $request->user()->employee,
            type: ProfileChangeType::Skill,
            subjectId: (int) $validated['skill_id'],
            payload: $payload,
            note: $validated['note'] ?? null,
            actor: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Skill change submitted for approval.']);

        return back();
    }
}
