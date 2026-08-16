<?php

namespace App\Filament\Admin\Resources\Projects\Schemas;

use App\Enums\AssignmentMode;
use App\Enums\ConfidentialityLevel;
use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General')
                    ->columns(2)
                    ->schema([
                        TextInput::make('project_code')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('priority')
                            ->options(ProjectPriority::class)
                            ->default(ProjectPriority::Medium->value)
                            ->required(),
                        Select::make('status')
                            ->options(ProjectStatus::class)
                            ->default(ProjectStatus::Draft->value)
                            ->required(),
                        Select::make('confidentiality_level')
                            ->options(ConfidentialityLevel::class)
                            ->default(ConfidentialityLevel::Internal->value)
                            ->required(),
                        Select::make('assignment_mode')
                            ->options(AssignmentMode::class)
                            ->default(AssignmentMode::MultipleEmployees->value)
                            ->required(),
                    ]),
                Section::make('Ownership')
                    ->columns(2)
                    ->schema([
                        Select::make('requesting_department_id')
                            ->label('Requesting department')
                            ->relationship('requestingDepartment', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('owning_department_id')
                            ->label('Owning department')
                            ->relationship('owningDepartment', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('project_manager_id')
                            ->label('Project manager')
                            ->relationship('projectManager', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable()
                            ->preload(),
                        Hidden::make('created_by')
                            ->default(fn () => Auth::id()),
                    ]),
                Section::make('Schedule & Budget')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('start_date'),
                        DatePicker::make('target_end_date')
                            ->afterOrEqual('start_date'),
                        TextInput::make('estimated_hours')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('estimated_budget')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$'),
                        TextInput::make('required_members_count')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                    ]),
            ]);
    }
}
