<?php

namespace App\Filament\Admin\Resources\Teams\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('team_leader_id')
                    ->label('Team leader')
                    ->relationship('teamLeader', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable()
                    ->preload(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
