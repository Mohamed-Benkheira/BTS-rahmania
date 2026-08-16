<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\EmploymentStatus;
use App\Models\Employee;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OverAllocatedEmployeesTable extends TableWidget
{
    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Over-Allocated Employees')
            ->description('Active employees whose allocated workload exceeds their current availability.')
            ->query($this->query())
            ->columns([
                TextColumn::make('full_name')
                    ->label('Employee')
                    ->getStateUsing(fn (Employee $record): string => $record->full_name)
                    ->tooltip('The employee who is currently over-allocated.'),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('—'),
                TextColumn::make('allocated')
                    ->label('Allocated %')
                    ->getStateUsing(fn (Employee $record): string => round((float) $record->allocated).'%')
                    ->badge()
                    ->color('danger')
                    ->tooltip('Total allocation percentage across current workload periods.'),
                TextColumn::make('available')
                    ->label('Available %')
                    ->getStateUsing(fn (Employee $record): string => round((float) $record->available).'%')
                    ->badge()
                    ->color('info')
                    ->tooltip('Current availability percentage, or 100% when no availability is recorded.'),
                TextColumn::make('overage')
                    ->label('Over by')
                    ->getStateUsing(fn (Employee $record): string => '+'.round((float) $record->overage).'%')
                    ->badge()
                    ->color(fn (Employee $record): string => (float) $record->overage >= 20 ? 'danger' : 'warning')
                    ->tooltip('How much the allocated workload exceeds the availability.'),
            ])
            ->paginated(false);
    }

    /**
     * @return Builder<Employee>
     */
    private function query(): Builder
    {
        $today = Carbon::today()->toDateString();

        $allocatedSql = 'COALESCE((
            SELECT SUM(allocated_percentage)
            FROM employee_workloads
            WHERE employee_id = employees.id
              AND period_start <= ?
              AND period_end >= ?
        ), 0)';

        $availableSql = 'COALESCE((
            SELECT MAX(availability_percentage)
            FROM employee_availabilities
            WHERE employee_id = employees.id
              AND start_date <= ?
              AND end_date >= ?
        ), 100)';

        return Employee::query()
            ->select('employees.*')
            ->selectRaw(
                "{$allocatedSql} as allocated, {$availableSql} as available, ({$allocatedSql} - {$availableSql}) as overage",
                [$today, $today, $today, $today, $today, $today, $today, $today],
            )
            ->where('employment_status', EmploymentStatus::Active->value)
            ->whereRaw("{$allocatedSql} > {$availableSql}", [$today, $today, $today, $today])
            ->orderByRaw('overage DESC')
            ->with('department')
            ->limit(10);
    }
}
