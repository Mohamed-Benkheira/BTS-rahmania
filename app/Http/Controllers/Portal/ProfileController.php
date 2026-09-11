<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ProfileChangeType;
use App\Http\Controllers\Controller;
use App\Services\ProfileChangeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'phone' => ['nullable', 'string', 'max:255'],
            'biography' => ['nullable', 'string', 'max:2000'],
            'birth_date' => ['nullable', 'date'],
        ]);

        $payload = array_filter($validated, fn ($value) => $value !== null);

        app(ProfileChangeRequestService::class)->submit(
            employee: $request->user()->employee,
            type: ProfileChangeType::Profile,
            payload: $payload,
            note: $request->input('note'),
            actor: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Profile change submitted for approval.']);

        return back();
    }
}
