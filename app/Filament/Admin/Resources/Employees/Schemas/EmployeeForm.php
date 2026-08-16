<?php

namespace App\Filament\Admin\Resources\Employees\Schemas;

use App\Enums\EmploymentStatus;
use App\Enums\EmploymentType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Personal information')
                    ->columns(3)
                    ->schema([
                        TextInput::make('employee_code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        DatePicker::make('birth_date'),
                        DatePicker::make('hire_date'),
                        Select::make('user_id')
                            ->label('User account')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('The authenticated user linked to this employee profile.'),
                        Select::make('employment_type')
                            ->options(EmploymentType::class)
                            ->default(EmploymentType::FullTime->value)
                            ->required(),
                        Select::make('employment_status')
                            ->options(EmploymentStatus::class)
                            ->default(EmploymentStatus::Active->value)
                            ->required(),
                        Textarea::make('biography')
                            ->columnSpanFull(),
                        TextInput::make('profile_photo_path')
                            ->maxLength(255),
                    ]),
                Section::make('Organization assignment')
                    ->columns(3)
                    ->schema([
                        Select::make('business_unit_id')
                            ->relationship('businessUnit', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('department_id')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('team_id')
                            ->relationship('team', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('position_id')
                            ->relationship('position', 'title')
                            ->searchable()
                            ->preload(),
                        Select::make('primary_location_id')
                            ->label('Primary location')
                            ->relationship('primaryLocation', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('manager_id')
                            ->relationship('manager', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}
