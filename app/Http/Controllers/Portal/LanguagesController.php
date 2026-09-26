<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ProfileChangeType;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\ProfileChangeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LanguagesController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;
        $employee->loadMissing('languages');

        $myLanguages = $employee->languages->map(function ($language) {
            return [
                ...$language->only(['id', 'name']),
                'employee_language' => $language->pivot?->only(['speaking_level', 'writing_level', 'reading_level']),
            ];
        })->values();

        return Inertia::render('portal/languages', [
            'languages' => Language::query()->orderBy('name')->get(['id', 'name']),
            'myLanguages' => $myLanguages,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $allowedLevels = ['beginner', 'intermediate', 'advanced', 'native'];

        $validated = $request->validate([
            'language_id' => ['required', 'integer', 'exists:languages,id'],
            'speaking_level' => ['nullable', 'string', Rule::in($allowedLevels)],
            'writing_level' => ['nullable', 'string', Rule::in($allowedLevels)],
            'reading_level' => ['nullable', 'string', Rule::in($allowedLevels)],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'language_id.required' => 'Please select a language.',
            'language_id.exists' => 'The selected language is invalid.',
            'speaking_level.in' => 'Selected speaking level is invalid.',
            'writing_level.in' => 'Selected writing level is invalid.',
            'reading_level.in' => 'Selected reading level is invalid.',
        ]);

        $payload = array_filter([
            'speaking_level' => $validated['speaking_level'] ?? null,
            'writing_level' => $validated['writing_level'] ?? null,
            'reading_level' => $validated['reading_level'] ?? null,
        ], fn ($val) => ! is_null($val) && $val !== '');

        if (empty($payload)) {
            throw ValidationException::withMessages([
                'speaking_level' => 'Please select at least one proficiency level (speaking, writing, or reading).',
            ]);
        }

        app(ProfileChangeRequestService::class)->submit(
            employee: $request->user()->employee,
            type: ProfileChangeType::Language,
            subjectId: (int) $validated['language_id'],
            payload: $payload,
            note: $validated['note'] ?? null,
            actor: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Language change submitted for approval.']);

        return back();
    }
}
