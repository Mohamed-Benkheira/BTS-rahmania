<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Account information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(table: 'users', column: 'email', ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->rules([Password::defaults()])
                            ->helperText('Leave blank to keep the current password.'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive users cannot access the admin panel.')
                            ->default(true),
                    ]),
                Section::make('Access')
                    ->columns(2)
                    ->schema([
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                        Select::make('employee')
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable()
                            ->preload()
                            ->helperText('Link this account to an employee profile.'),
                    ]),
            ]);
    }
}
