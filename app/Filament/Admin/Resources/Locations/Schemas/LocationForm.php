<?php

namespace App\Filament\Admin\Resources\Locations\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('city'),
                TextInput::make('country'),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('timezone'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
