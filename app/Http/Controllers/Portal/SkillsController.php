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
        $payload = $request->validate([
            'proficiency_level' => ['required', 'integer', 'between:1,5'],
            'years_experience' => ['nullable', 'numeric', 'min:0'],
            'last_used_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        app(ProfileChangeRequestService::class)->submit(
            employee: $request->user()->employee,
            type: ProfileChangeType::Skill,
            subjectId: (int) $request->input('skill_id'),
            payload: $payload,
            note: $request->input('note'),
            actor: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Skill change submitted for approval.']);

        return back();
    }
}
