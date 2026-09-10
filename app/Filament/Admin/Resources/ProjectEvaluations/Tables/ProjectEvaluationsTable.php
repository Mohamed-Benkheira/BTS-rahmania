<?php

namespace App\Filament\Admin\Resources\ProjectEvaluations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectEvaluationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.full_name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('evaluator.name')
                    ->label('Evaluator')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->sortable(),
                TextColumn::make('communication_rating')
                    ->label('Comm')
                    ->sortable(),
                TextColumn::make('delivery_rating')
                    ->label('Delivery')
                    ->sortable(),
                TextColumn::make('quality_rating')
                    ->label('Quality')
                    ->sortable(),
                TextColumn::make('evaluated_at')
                    ->label('Evaluated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('evaluated_at', 'desc')
            ->filters([
                //
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
