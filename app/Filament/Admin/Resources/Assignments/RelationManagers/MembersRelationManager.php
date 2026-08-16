<?php

namespace App\Filament\Admin\Resources\Assignments\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('responsibility')
                    ->maxLength(255),
                TextInput::make('allocation_percentage')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                TextInput::make('allocated_hours')
                    ->numeric()
                    ->minValue(0),
                DatePicker::make('joined_at'),
                DatePicker::make('left_at')
                    ->afterOrEqual('joined_at'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                TextColumn::make('full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position.name')
                    ->label('Position')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('pivot.responsibility')
                    ->label('Responsibility')
                    ->placeholder('—'),
                TextColumn::make('pivot.allocation_percentage')
                    ->label('Allocation')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('pivot.joined_at')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
                TextColumn::make('pivot.left_at')
                    ->label('Left')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['first_name', 'last_name']),
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
