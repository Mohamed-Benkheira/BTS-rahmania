<?php

namespace App\Filament\Admin\Resources\Positions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PositionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('level')
                    ->options([
                        'intern' => 'Intern',
                        'junior' => 'Junior',
                        'mid' => 'Mid',
                        'senior' => 'Senior',
                        'lead' => 'Lead',
                        'manager' => 'Manager',
                        'director' => 'Director',
                    ]),
                Textarea::make('description')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
