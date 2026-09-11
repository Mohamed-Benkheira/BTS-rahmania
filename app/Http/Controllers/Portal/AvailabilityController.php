<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ProfileChangeType;
use App\Http\Controllers\Controller;
use App\Services\ProfileChangeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;

        return Inertia::render('portal/availability', [
            'availabilities' => $employee->availabilities()
                ->orderByDesc('start_date')
                ->limit(20)
                ->get(),
            'pendingRequests' => $employee->profileChangeRequests()
                ->where('type', ProfileChangeType::Availability->value)
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->get(['id', 'status', 'payload', 'created_at']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'availability_percentage' => ['required', 'numeric', 'between:0,100'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        app(ProfileChangeRequestService::class)->submit(
            employee: $request->user()->employee,
            type: ProfileChangeType::Availability,
            payload: $payload,
            note: $request->input('note'),
            actor: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Availability change submitted for approval.']);

        return back();
    }
}
