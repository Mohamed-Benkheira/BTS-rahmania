<?php

namespace App\Filament\Admin\Resources\Employees\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SkillsRelationManager extends RelationManager
{
    protected static string $relationship = 'skills';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('proficiency_level')
                    ->label('Proficiency')
                    ->options([
                        1 => '1 - Novice',
                        2 => '2 - Basic',
                        3 => '3 - Intermediate',
                        4 => '4 - Advanced',
                        5 => '5 - Expert',
                    ])
                    ->required(),
                TextInput::make('years_experience')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.5),
                DatePicker::make('last_used_at'),
                DateTimePicker::make('verified_at')
                    ->label('Verified at'),
                Select::make('verified_by')
                    ->relationship('verifier', 'name')
                    ->searchable()
                    ->preload(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('pivot.proficiency_level')
                    ->label('Proficiency')
                    ->badge()
                    ->sortable(),
                TextColumn::make('pivot.years_experience')
                    ->label('Years')
                    ->sortable(),
                TextColumn::make('pivot.last_used_at')
                    ->label('Last used')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name']),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
