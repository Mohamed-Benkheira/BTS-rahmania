<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\AssignmentStatus;
use App\Enums\CertificationVerificationStatus;
use App\Enums\EmploymentStatus;
use App\Enums\ProjectStatus;
use App\Models\Assignment;
use App\Models\Employee;
use App\Models\EmployeeCertification;
use App\Models\Project;
use App\Support\Tooltip;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        return [
            $this->activeEmployeesStat(),
            $this->activeProjectsStat(),
            $this->activeAssignmentsStat(),
            $this->expiringCertificationsStat(),
        ];
    }

    private function activeEmployeesStat(): Stat
    {
        $count = Employee::where('employment_status', EmploymentStatus::Active->value)->count();

        $chart = collect(range(5, 0))
            ->map(fn (int $monthsAgo): int => Employee::whereBetween('hire_date', [
                Carbon::today()->subMonths($monthsAgo + 1)->startOfMonth(),
                Carbon::today()->subMonths($monthsAgo)->endOfMonth(),
            ])->count())
            ->all();

        return Stat::make(
            new Tooltip('Active Employees', 'Employees currently marked as active on payroll.'),
            $count,
        )
            ->description('Currently on payroll')
            ->descriptionIcon('heroicon-m-user-group', 'before')
            ->descriptionColor('success')
            ->chart($chart)
            ->chartColor('success')
            ->color('success');
    }

    private function activeProjectsStat(): Stat
    {
        $inProgress = Project::where('status', ProjectStatus::InProgress->value)->count();
        $staffing = Project::where('status', ProjectStatus::Staffing->value)->count();

        $chart = collect(range(5, 0))
            ->map(fn (int $monthsAgo): int => Project::whereBetween('created_at', [
                Carbon::now()->subMonths($monthsAgo + 1)->startOfMonth(),
                Carbon::now()->subMonths($monthsAgo)->endOfMonth(),
            ])->count())
            ->all();

        return Stat::make(
            new Tooltip('Active Projects', 'Projects that are currently in progress.'),
            $inProgress,
        )
            ->description(new Tooltip(
                "{$staffing} in staffing",
                'Projects that still need employees or teams to be assigned.',
            ))
            ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
            ->descriptionColor('info')
            ->chart($chart)
            ->chartColor('info')
            ->color('info');
    }

    private function activeAssignmentsStat(): Stat
    {
        $active = Assignment::where('status', AssignmentStatus::Active->value)->count();
        $pending = Assignment::where('status', AssignmentStatus::Pending->value)->count();

        $chart = collect(range(5, 0))
            ->map(fn (int $monthsAgo): int => Assignment::whereBetween('created_at', [
                Carbon::now()->subMonths($monthsAgo + 1)->startOfMonth(),
                Carbon::now()->subMonths($monthsAgo)->endOfMonth(),
            ])->count())
            ->all();

        return Stat::make(
            new Tooltip('Active Assignments', 'Assignments that are currently active.'),
            $active,
        )
            ->description(new Tooltip(
                "{$pending} pending approval",
                'Assignments that have been created but not yet approved.',
            ))
            ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
            ->descriptionColor('warning')
            ->chart($chart)
            ->chartColor('warning')
            ->color('warning');
    }

    private function expiringCertificationsStat(): Stat
    {
        $horizon = Carbon::today()->addDays(60);
        $count = EmployeeCertification::query()
            ->where('verification_status', CertificationVerificationStatus::Verified->value)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [Carbon::today(), $horizon])
            ->count();

        $chart = collect(range(5, 0))
            ->map(fn (int $monthsAgo): int => EmployeeCertification::query()
                ->whereNotNull('expires_at')
                ->whereBetween('expires_at', [
                    Carbon::today()->addMonths($monthsAgo)->startOfMonth(),
                    Carbon::today()->addMonths($monthsAgo)->endOfMonth(),
                ])
                ->count())
            ->all();

        return Stat::make(
            new Tooltip('Certifications Expiring Soon', 'Verified certifications that expire within the next 60 days.'),
            $count,
        )
            ->description('Within the next 60 days')
            ->descriptionIcon('heroicon-m-exclamation-triangle', 'before')
            ->descriptionColor($count > 0 ? 'danger' : 'success')
            ->chart($chart)
            ->chartColor('danger')
            ->color($count > 0 ? 'danger' : 'success');
    }
}
