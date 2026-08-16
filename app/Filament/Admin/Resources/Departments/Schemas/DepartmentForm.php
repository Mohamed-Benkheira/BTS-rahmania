<?php

namespace App\Filament\Admin\Resources\Departments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('business_unit_id')
                    ->relationship('businessUnit', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('parent_department_id')
                    ->label('Parent department')
                    ->relationship('parentDepartment', 'name')
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
                Select::make('manager_id')
                    ->label('Manager')
                    ->relationship('manager', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable()
                    ->preload(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
