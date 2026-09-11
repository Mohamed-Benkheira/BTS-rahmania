<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssignmentsController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;

        $assignments = $employee->assignments()
            ->with(['project', 'statusHistory' => fn ($q) => $q->latest()])
            ->latest('assignment_members.joined_at')
            ->get()
            ->map(function ($assignment) {
                return [
                    ...$assignment->only(['id', 'status', 'assignment_type', 'start_date', 'end_date', 'assigned_at']),
                    'project' => $assignment->project?->only(['id', 'name', 'status']),
                    'responsibility' => $assignment->pivot?->responsibility,
                    'allocation_percentage' => $assignment->pivot?->allocation_percentage,
                ];
            })
            ->values();

        return Inertia::render('portal/assignments', [
            'assignments' => $assignments,
        ]);
    }
}
