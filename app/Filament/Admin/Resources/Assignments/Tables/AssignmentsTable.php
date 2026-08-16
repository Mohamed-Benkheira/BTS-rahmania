<?php

namespace App\Filament\Admin\Resources\Assignments\Tables;

use App\Enums\AssignmentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('members_count')
                    ->label('Members')
                    ->counts('members')
                    ->sortable(),
                TextColumn::make('assignment_type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?AssignmentStatus $state): string => match ($state) {
                        AssignmentStatus::Pending => 'warning',
                        AssignmentStatus::Approved => 'info',
                        AssignmentStatus::Active => 'success',
                        AssignmentStatus::Completed => 'gray',
                        AssignmentStatus::Rejected,
                        AssignmentStatus::Cancelled => 'danger',
                        null => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('assigner.name')
                    ->label('Assigned by')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(AssignmentStatus::class),
                SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
