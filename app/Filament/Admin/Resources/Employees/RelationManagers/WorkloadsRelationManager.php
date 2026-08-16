<?php

namespace App\Filament\Admin\Resources\Employees\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkloadsRelationManager extends RelationManager
{
    protected static string $relationship = 'workloads';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('period_start')
                    ->required(),
                DatePicker::make('period_end')
                    ->required()
                    ->afterOrEqual('period_start'),
                TextInput::make('allocated_percentage')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->required(),
                TextInput::make('allocated_hours')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('source')
                    ->default('manual')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('period_start')
                    ->date()
                    ->sortable(),
                TextColumn::make('period_end')
                    ->date()
                    ->sortable(),
                TextColumn::make('allocated_percentage')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('allocated_hours')
                    ->sortable(),
                TextColumn::make('source')
                    ->badge(),
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
