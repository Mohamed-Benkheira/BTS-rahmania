<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\EmploymentStatus;
use App\Models\Department;
use Filament\Widgets\ChartWidget;

class EmployeesByDepartmentChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 2;

    public function getHeading(): string
    {
        return 'Employees by Department';
    }

    public function getDescription(): string
    {
        return 'Active employees per department (top 10).';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $rows = Department::query()
            ->withCount([
                'employees' => fn ($query) => $query->where('employment_status', EmploymentStatus::Active->value),
            ])
            ->orderByDesc('employees_count')
            ->limit(10)
            ->get();

        return [
            'labels' => $rows->map(fn (Department $department): string => $department->name)->all(),
            'datasets' => [
                [
                    'label' => 'Active Employees',
                    'data' => $rows->map(fn (Department $department): int => $department->employees_count)->all(),
                    'backgroundColor' => '#f59e0b',
                ],
            ],
        ];
    }
}
