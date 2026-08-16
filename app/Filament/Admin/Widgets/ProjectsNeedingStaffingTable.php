<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Support\EnumLabels;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ProjectsNeedingStaffingTable extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Projects Needing Staffing')
            ->description('Projects waiting for employees or teams to be assigned.')
            ->query(
                Project::query()
                    ->whereIn('status', [
                        ProjectStatus::Submitted->value,
                        ProjectStatus::UnderReview->value,
                        ProjectStatus::Staffing->value,
                    ])
                    ->withCount('requirements')
                    ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 ELSE 5 END")
                    ->orderBy('target_end_date')
                    ->limit(10),
            )
            ->columns([
                TextColumn::make('project_code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->tooltip('Unique project code.'),
                TextColumn::make('name')
                    ->label('Project')
                    ->searchable()
                    ->tooltip('Project name.'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (ProjectStatus $state): string => match ($state) {
                        ProjectStatus::Staffing => 'warning',
                        ProjectStatus::UnderReview => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (ProjectStatus $state): string => EnumLabels::friendly($state))
                    ->tooltip('Current lifecycle status.'),
                TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn (ProjectPriority $state): string => match ($state) {
                        ProjectPriority::Critical => 'danger',
                        ProjectPriority::High => 'warning',
                        ProjectPriority::Medium => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (ProjectPriority $state): string => EnumLabels::friendly($state))
                    ->tooltip('How urgent the project is.'),
                TextColumn::make('required_members_count')
                    ->label('Members Needed')
                    ->alignCenter()
                    ->tooltip('Total number of members required for this project.'),
                TextColumn::make('requirements_count')
                    ->label('Requirements')
                    ->alignCenter()
                    ->tooltip('Number of recorded project requirements.'),
                TextColumn::make('target_end_date')
                    ->label('Target End')
                    ->date()
                    ->tooltip('The planned end date of the project.'),
            ])
            ->paginated(false);
    }
}
