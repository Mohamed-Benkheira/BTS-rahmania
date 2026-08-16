<?php

namespace App\Filament\Admin\Resources\Employees\RelationManagers;

use App\Enums\LanguageLevel;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LanguagesRelationManager extends RelationManager
{
    protected static string $relationship = 'languages';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('speaking_level')
                    ->options(LanguageLevel::class),
                Select::make('writing_level')
                    ->options(LanguageLevel::class),
                Select::make('reading_level')
                    ->options(LanguageLevel::class),
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
                TextColumn::make('pivot.speaking_level')
                    ->label('Speaking')
                    ->formatStateUsing(fn (LanguageLevel $state): string => ucfirst($state->value)),
                TextColumn::make('pivot.writing_level')
                    ->label('Writing')
                    ->formatStateUsing(fn (LanguageLevel $state): string => ucfirst($state->value)),
                TextColumn::make('pivot.reading_level')
                    ->label('Reading')
                    ->formatStateUsing(fn (LanguageLevel $state): string => ucfirst($state->value)),
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
