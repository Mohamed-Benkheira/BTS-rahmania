<?php

namespace App\Filament\Admin\Resources\Projects\RelationManagers;

use App\Enums\LanguageLevel;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RequiredLanguagesRelationManager extends RelationManager
{
    protected static string $relationship = 'requiredLanguages';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('minimum_level')
                    ->label('Minimum level')
                    ->options(LanguageLevel::class),
                Toggle::make('is_mandatory')
                    ->default(true),
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
                TextColumn::make('pivot.minimum_level')
                    ->label('Min. level')
                    ->formatStateUsing(fn (LanguageLevel $state): string => ucfirst($state->value)),
                IconColumn::make('pivot.is_mandatory')
                    ->label('Mandatory')
                    ->boolean(),
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
