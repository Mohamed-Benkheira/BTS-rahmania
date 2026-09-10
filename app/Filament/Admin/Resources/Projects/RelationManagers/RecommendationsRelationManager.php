<?php

namespace App\Filament\Admin\Resources\Projects\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecommendationsRelationManager extends RelationManager
{
    protected static string $relationship = 'recommendationRuns';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('Run #')
                    ->sortable()
                    ->badge(),
                TextColumn::make('executor.name')
                    ->label('Run by')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('algorithm_version')
                    ->label('Algorithm')
                    ->sortable(),
                TextColumn::make('criteria_snapshot.assignment_mode')
                    ->label('Mode')
                    ->formatStateUsing(fn ($state) => str_replace('_', ' ', ucfirst($state ?? '')))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'team' => Color::Blue,
                        'department' => Color::Purple,
                        default => Color::Green,
                    }),
                TextColumn::make('recommendations_count')
                    ->label('Candidates')
                    ->counts('recommendations')
                    ->sortable(),
                TextColumn::make('executed_at')
                    ->label('Executed')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('view_recommendations')
                    ->label('View Rankings')
                    ->icon('heroicon-o-list-bullet')
                    ->color(Color::Blue)
                    ->modalHeading(fn ($record) => "Recommendation Run #{$record->id} (".($record->criteria_snapshot['assignment_mode'] ?? 'employee').' mode)')
                    ->modalContent(fn ($record) => view('filament.admin.recommendations-modal', [
                        'recommendations' => $record->recommendations()
                            ->with(['employee', 'team', 'department'])
                            ->orderBy('rank')
                            ->get(),
                    ]))
                    ->modalSubmitAction(false),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                ActionGroup::make([]),
            ]);
    }
}
