<?php

namespace App\Filament\Admin\Resources\Projects\RelationManagers;

use App\Enums\AssignmentType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('members'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('assignment_type')
                    ->label('Type')
                    ->getStateUsing(fn ($record) => $record->assignment_type?->value)
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        AssignmentType::Team->value => 'blue',
                        AssignmentType::Department->value => 'purple',
                        AssignmentType::Mixed->value => 'gray',
                        default => 'green',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('status')
                    ->getStateUsing(fn ($record) => $record->status?->value)
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'approved' => 'info',
                        'pending' => 'warning',
                        'completed' => 'gray',
                        default => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('members_count')
                    ->label('Members')
                    ->sortable()
                    ->formatStateUsing(fn (int $state) => $state ?: '—'),
                TextColumn::make('start_date')
                    ->date('Y-m-d')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date('Y-m-d')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('assigner.name')
                    ->label('Assigned by')
                    ->placeholder('—'),
                TextColumn::make('approved_at')
                    ->dateTime('Y-m-d H:i')
                    ->placeholder('Not approved')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('assignment_type')
                    ->label('Type')
                    ->options([
                        AssignmentType::Employee->value => 'Employee',
                        AssignmentType::Team->value => 'Team',
                        AssignmentType::Department->value => 'Department',
                        AssignmentType::Mixed->value => 'Mixed',
                    ]),
            ]);
    }
}
