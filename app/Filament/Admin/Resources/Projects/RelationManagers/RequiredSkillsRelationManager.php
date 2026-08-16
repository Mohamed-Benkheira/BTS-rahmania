<?php

namespace App\Filament\Admin\Resources\Projects\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RequiredSkillsRelationManager extends RelationManager
{
    protected static string $relationship = 'requiredSkills';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('minimum_proficiency')
                    ->label('Minimum proficiency')
                    ->options([
                        1 => '1 - Novice',
                        2 => '2 - Basic',
                        3 => '3 - Intermediate',
                        4 => '4 - Advanced',
                        5 => '5 - Expert',
                    ]),
                TextInput::make('minimum_years_experience')
                    ->label('Min. years experience')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.5),
                Toggle::make('is_mandatory')
                    ->default(true),
                TextInput::make('weight')
                    ->numeric()
                    ->default(1)
                    ->step(0.1)
                    ->minValue(0),
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
                TextColumn::make('pivot.minimum_proficiency')
                    ->label('Min. proficiency')
                    ->sortable(),
                TextColumn::make('pivot.minimum_years_experience')
                    ->label('Min. years')
                    ->sortable(),
                IconColumn::make('pivot.is_mandatory')
                    ->label('Mandatory')
                    ->boolean(),
                TextColumn::make('pivot.weight')
                    ->numeric()
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
