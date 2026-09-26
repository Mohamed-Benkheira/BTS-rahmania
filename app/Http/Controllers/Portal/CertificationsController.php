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
        $validated = $request->validate([
            'certification_id' => ['required', 'integer', 'exists:certifications,id'],
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'issued_at' => ['nullable', 'date', 'before_or_equal:today'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'document' => ['nullable', 'file', 'mimes:pdf,png,jpg,jpeg', 'max:4096'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'certification_id.required' => 'Please select a certification.',
            'certification_id.exists' => 'The selected certification is invalid.',
            'issued_at.before_or_equal' => 'Issued date cannot be in the future.',
            'expires_at.after_or_equal' => 'Expires date must be on or after the issued date.',
            'document.max' => 'The document may not be greater than 4MB.',
            'document.mimes' => 'The document must be a file of type: pdf, png, jpg, jpeg.',
        ]);

        $payload = [
            'certificate_number' => $validated['certificate_number'] ?? null,
            'issued_at' => $validated['issued_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ];

        if ($request->hasFile('document')) {
            $payload['document_path'] = $request->file('document')->store('certification-documents', 'public');
        }

        app(ProfileChangeRequestService::class)->submit(
            employee: $request->user()->employee,
            type: ProfileChangeType::Certification,
            subjectId: (int) $validated['certification_id'],
            payload: $payload,
            note: $validated['note'] ?? null,
            actor: $request->user(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Certification change submitted for approval.']);

        return back();
    }
}
