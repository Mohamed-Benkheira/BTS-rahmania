<?php

namespace App\Filament\Admin\Resources\Projects\Tables;

use App\Enums\ConfidentialityLevel;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->project_code),
                TextColumn::make('category.name')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('projectManager.full_name')
                    ->label('Project manager')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (?ProjectPriority $state): string => match ($state) {
                        ProjectPriority::Critical => 'danger',
                        ProjectPriority::High => 'warning',
                        ProjectPriority::Medium => 'info',
                        ProjectPriority::Low => 'gray',
                        null => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?ProjectStatus $state): string => match ($state) {
                        ProjectStatus::Draft => 'gray',
                        ProjectStatus::Submitted => 'info',
                        ProjectStatus::UnderReview => 'warning',
                        ProjectStatus::Staffing,
                        ProjectStatus::Assigned => 'primary',
                        ProjectStatus::InProgress,
                        ProjectStatus::Completed => 'success',
                        ProjectStatus::Cancelled,
                        ProjectStatus::Archived => 'danger',
                        null => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('confidentiality_level')
                    ->label('Confidentiality')
                    ->badge()
                    ->color(fn (?ConfidentialityLevel $state): string => match ($state) {
                        ConfidentialityLevel::Public => 'gray',
                        ConfidentialityLevel::Internal => 'info',
                        ConfidentialityLevel::Confidential => 'warning',
                        ConfidentialityLevel::Restricted => 'danger',
                        null => 'gray',
                    })
                    ->toggleable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('target_end_date')
                    ->label('Target end')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('required_members_count')
                    ->label('Members')
                    ->sortable(),
                TextColumn::make('estimated_hours')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('estimated_budget')
                    ->numeric()
                    ->prefix('$')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ProjectStatus::class),
                SelectFilter::make('priority')
                    ->options(ProjectPriority::class),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
