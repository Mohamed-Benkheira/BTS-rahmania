<?php

namespace App\Filament\Admin\Resources\Assignments\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistory';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('new_status')
            ->columns([
                TextColumn::make('old_status')
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state->value)) : '—')
                    ->placeholder('—'),
                TextColumn::make('new_status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? ucfirst(str_replace('_', ' ', $state->value)) : '—'),
                TextColumn::make('changer.name')
                    ->label('Changed by')
                    ->sortable(),
                TextColumn::make('reason')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->recordActions([]);
    }
}
