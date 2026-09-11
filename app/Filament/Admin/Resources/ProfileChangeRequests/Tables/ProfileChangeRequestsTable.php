<?php

namespace App\Filament\Admin\Resources\ProfileChangeRequests\Tables;

use App\Enums\ProfileChangeStatus;
use App\Enums\ProfileChangeType;
use App\Filament\Admin\Resources\ProfileChangeRequests\ProfileChangeRequestResource;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProfileChangeRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ProfileChangeRequestResource::getUrl('view', ['record' => $record])),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (ProfileChangeType $state): string => ucfirst($state->value))
                    ->sortable(),
                TextColumn::make('subjectDisplay')
                    ->label('Subject')
                    ->searchable()
                    ->state(fn ($record) => $record->subjectDisplay()),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (ProfileChangeStatus $state): string => match ($state) {
                        ProfileChangeStatus::Pending => 'warning',
                        ProfileChangeStatus::Approved => 'success',
                        ProfileChangeStatus::Rejected => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewer.name')
                    ->label('Reviewed by')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options(ProfileChangeType::class),
                SelectFilter::make('status')
                    ->options(ProfileChangeStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
