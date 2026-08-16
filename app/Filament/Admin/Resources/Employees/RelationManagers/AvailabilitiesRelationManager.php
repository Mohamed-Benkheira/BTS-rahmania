<?php

namespace App\Filament\Admin\Resources\Employees\RelationManagers;

use App\Enums\AvailabilityStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AvailabilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'availabilities';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required()
                    ->afterOrEqual('start_date'),
                TextInput::make('availability_percentage')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),
                Select::make('status')
                    ->options(AvailabilityStatus::class)
                    ->default(AvailabilityStatus::Available->value)
                    ->required(),
                TextInput::make('reason')
                    ->maxLength(255),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('availability_percentage')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?AvailabilityStatus $state): string => match ($state) {
                        AvailabilityStatus::Available => 'success',
                        AvailabilityStatus::PartiallyAvailable => 'warning',
                        AvailabilityStatus::Unavailable => 'danger',
                        AvailabilityStatus::OnLeave => 'gray',
                        null => 'gray',
                    }),
                TextColumn::make('reason')
                    ->placeholder('—'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
