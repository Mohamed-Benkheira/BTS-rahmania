<?php

namespace App\Http\Controllers\Portal;

use App\Enums\ProfileChangeType;
use App\Http\Controllers\Controller;
use App\Models\Certification;
use App\Services\ProfileChangeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificationsController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;
        $employee->loadMissing('certifications');

        $myCertifications = $employee->certifications->map(function ($certification) {
            return [
                ...$certification->only(['id', 'name', 'issuer']),
                'certificate' => $certification->pivot?->only([
                    'certificate_number', 'issued_at', 'expires_at', 'verification_status', 'document_path',
                ]),
            ];
        })->values();

        return Inertia::render('portal/certifications', [
            'certifications' => Certification::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'issuer', 'description']),
            'myCertifications' => $myCertifications,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'document' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:4096'],
        ]);

        unset($payload['document']);

        if ($request->hasFile('document')) {
            $payload['document_path'] = $request->file('document')->store('certification-documents', 'public');
        }

        app(ProfileChangeRequestService::class)->submit(
            employee: $request->user()->employee,
            type: ProfileChangeType::Certification,
            subjectId: (int) $request->input('certification_id'),
            payload: $payload,
            note: $request->input('note'),
            actor: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Certification change submitted for approval.']);

        return back();
    }
}
