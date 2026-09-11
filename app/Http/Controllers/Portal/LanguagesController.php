<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ProfileChangeType;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Services\ProfileChangeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $payload = $request->validate([
            'speaking_level' => ['nullable', 'string', 'max:20'],
            'writing_level' => ['nullable', 'string', 'max:20'],
            'reading_level' => ['nullable', 'string', 'max:20'],
        ]);

        app(ProfileChangeRequestService::class)->submit(
            employee: $request->user()->employee,
            type: ProfileChangeType::Language,
            subjectId: (int) $request->input('language_id'),
            payload: $payload,
            note: $request->input('note'),
            actor: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Language change submitted for approval.']);

        return back();
    }
}
