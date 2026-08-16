<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\EmploymentStatus;
use App\Models\Department;
use App\Models\Employee;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AllocationVsAvailabilityChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    public function getHeading(): string
    {
        return 'Workload vs. Availability';
    }

    public function getDescription(): string
    {
        return 'Allocated workload versus current availability per department. Red above green means over-allocation.';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $today = Carbon::today();

        $departments = Department::query()
            ->with(['employees' => function ($query) use ($today): void {
                $query
                    ->where('employment_status', EmploymentStatus::Active->value)
                    ->with(['workloads' => fn ($q) => $q->whereDate('period_start', '<=', $today)->whereDate('period_end', '>=', $today)])
                    ->with(['availabilities' => fn ($q) => $q->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)]);
            }])
            ->get()
            ->filter(fn (Department $department): bool => $department->employees->isNotEmpty())
            ->take(10);

        $labels = [];
        $allocated = [];
        $available = [];

        foreach ($departments as $department) {
            $labels[] = $department->name;

            $allocatedSum = 0;
            $availabilitySum = 0;
            $availabilityCount = 0;

            foreach ($department->employees as $employee) {
                /** @var Employee $employee */
                $allocatedSum += $employee->workloads->sum('allocated_percentage');

                $currentAvailability = $employee->availabilities->first();

                if ($currentAvailability?->availability_percentage !== null) {
                    $availabilitySum += $currentAvailability->availability_percentage;
                    $availabilityCount++;
                }
            }

            $allocated[] = round($allocatedSum, 1);
            $available[] = $availabilityCount > 0 ? round($availabilitySum / $availabilityCount, 1) : 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Allocated %',
                    'data' => $allocated,
                    'backgroundColor' => '#ef4444',
                ],
                [
                    'label' => 'Available %',
                    'data' => $available,
                    'backgroundColor' => '#22c55e',
                ],
            ],
        ];
    }
}
