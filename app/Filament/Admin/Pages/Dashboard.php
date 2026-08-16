<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\AllocationVsAvailabilityChart;
use App\Filament\Admin\Widgets\EmployeesByDepartmentChart;
use App\Filament\Admin\Widgets\ExpiringCertificationsTable;
use App\Filament\Admin\Widgets\OverAllocatedEmployeesTable;
use App\Filament\Admin\Widgets\ProjectsByStatusChart;
use App\Filament\Admin\Widgets\ProjectsNeedingStaffingTable;
use App\Filament\Admin\Widgets\RecentActivityTable;
use App\Filament\Admin\Widgets\StatsOverview;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = 0;

    public function getTitle(): string
    {
        return 'Resource Assignment Dashboard';
    }

    /**
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            EmployeesByDepartmentChart::class,
            ProjectsByStatusChart::class,
            AllocationVsAvailabilityChart::class,
            RecentActivityTable::class,
            ExpiringCertificationsTable::class,
            OverAllocatedEmployeesTable::class,
            ProjectsNeedingStaffingTable::class,
        ];
    }

    public function getColumns(): int
    {
        return 4;
    }
}
