<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ProfileChangeType;
use App\Http\Controllers\Controller;
use App\Services\ProfileChangeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(Request $request): Response
    {
        $employee = $request->user()->employee;
        $employee->loadMissing(['position', 'department', 'team', 'manager', 'businessUnit']);

        return Inertia::render('portal/profile', [
            'employee' => $employee,
            'pendingRequests' => $employee->profileChangeRequests()
                ->where('type', ProfileChangeType::Profile->value)
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^([+]?[0-9\s\-().]{6,30})?$/'],
            'biography' => ['nullable', 'string', 'max:2000'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'phone.regex' => 'The phone number format is invalid.',
            'birth_date.before' => 'The birth date must be a date in the past.',
        ]);

        $payload = array_filter([
            'phone' => $validated['phone'] ?? null,
            'biography' => $validated['biography'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');

        if (empty($payload)) {
            throw ValidationException::withMessages([
                'phone' => 'Please provide at least one field to update (phone, birth date, or biography).',
            ]);
        }

        app(ProfileChangeRequestService::class)->submit(
            employee: $request->user()->employee,
            type: ProfileChangeType::Profile,
            payload: $payload,
            note: $validated['note'] ?? null,
            actor: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Profile change submitted for approval.']);

        return back();
    }
}
