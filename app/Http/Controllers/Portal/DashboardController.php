<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAvailability;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $employee = $request->user()->employee;
        $employee->loadMissing(['position', 'department', 'team', 'manager']);

        $currentAssignment = $employee->assignments()
            ->with(['project'])
            ->whereIn('status', ['approved', 'active'])
            ->latest('id')
            ->first();

        $latestAvailability = EmployeeAvailability::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('start_date')
            ->first();

        return Inertia::render('portal/dashboard', [
            'employee' => $employee,
            'currentAssignment' => $currentAssignment,
            'latestAvailability' => $latestAvailability,
            'stats' => [
                'skills' => $employee->skills()->count(),
                'languages' => $employee->languages()->count(),
                'certifications' => $employee->certifications()->count(),
                'pending_requests' => $employee->profileChangeRequests()->where('status', 'pending')->count(),
                'unread_notifications' => $request->user()->unreadNotifications()->count(),
            ],
        ]);
    }
}
