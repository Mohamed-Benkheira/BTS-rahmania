<?php

namespace App\Filament\Admin\Resources\Employees\Tables;

use App\Enums\EmploymentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_code')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('Name')
                    ->getStateUsing(fn ($record) => $record->full_name)
                    ->searchable(query: fn ($query, $search) => $query->where(
                        fn ($q) => $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                    ))
                    ->sortable(query: fn ($query) => $query->orderBy('first_name')->orderBy('last_name')),
                TextColumn::make('position.title')
                    ->label('Position')
                    ->searchable(),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable(),
                TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable(),
                TextColumn::make('employment_type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state->value)) : '—'),
                TextColumn::make('employment_status')
                    ->badge()
                    ->color(fn (?EmploymentStatus $state): string => match ($state) {
                        EmploymentStatus::Active => 'success',
                        EmploymentStatus::OnLeave => 'warning',
                        EmploymentStatus::Suspended => 'danger',
                        EmploymentStatus::Resigned,
                        EmploymentStatus::Retired => 'gray',
                        null => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state->value)) : '—'),
                TextColumn::make('manager')
                    ->label('Manager')
                    ->getStateUsing(fn ($record) => $record->manager?->full_name)
                    ->placeholder('—'),
                TextColumn::make('hire_date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('employment_status')
                    ->options([
                        'active' => 'Active',
                        'on_leave' => 'On leave',
                        'suspended' => 'Suspended',
                        'resigned' => 'Resigned',
                        'retired' => 'Retired',
                    ]),
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name'),
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
