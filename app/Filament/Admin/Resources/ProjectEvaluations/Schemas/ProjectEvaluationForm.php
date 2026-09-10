<?php

namespace App\Filament\Admin\Resources\ProjectEvaluations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProjectEvaluationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('project_id')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('employee_id')
                    ->relationship('employee', 'employee_code')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('evaluator_id')
                    ->relationship('evaluator', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('rating')
                    ->label('Overall Rating (1-5)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->required(),
                TextInput::make('communication_rating')
                    ->label('Communication (1-5)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->nullable(),
                TextInput::make('delivery_rating')
                    ->label('Delivery (1-5)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->nullable(),
                TextInput::make('quality_rating')
                    ->label('Quality (1-5)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(5)
                    ->nullable(),
                Textarea::make('comments')
                    ->columnSpanFull(),
                DateTimePicker::make('evaluated_at')
                    ->required(),
            ]);
    }
}
