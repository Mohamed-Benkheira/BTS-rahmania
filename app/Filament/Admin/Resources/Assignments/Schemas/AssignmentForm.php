<?php

namespace App\Filament\Admin\Resources\Assignments\Schemas;

use App\Enums\AssignmentStatus;
use App\Enums\AssignmentType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Assignment')
                    ->columns(2)
                    ->schema([
                        Select::make('project_id')
                            ->label('Project')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('assignment_type')
                            ->options(AssignmentType::class)
                            ->default(AssignmentType::Employee->value)
                            ->required(),
                        Select::make('status')
                            ->options(AssignmentStatus::class)
                            ->default(AssignmentStatus::Pending->value)
                            ->required()
                            ->disabled()
                            ->dehydrated(),
                        DatePicker::make('start_date'),
                        DatePicker::make('end_date')
                            ->afterOrEqual('start_date'),
                        DateTimePicker::make('assigned_at')
                            ->default(now()),
                        DateTimePicker::make('approved_at')
                            ->disabled()
                            ->dehydrated(),
                        Hidden::make('assigned_by')
                            ->default(fn () => Auth::id()),
                        Textarea::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
