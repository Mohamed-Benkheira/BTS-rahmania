<?php

namespace App\Filament\Admin\Resources\Roles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('permissions')
                    ->relationship('permissions', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
