<?php

namespace App\Filament\Admin\Resources\Employees\RelationManagers;

use App\Enums\CertificationVerificationStatus;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CertificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'certifications';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('certificate_number')
                    ->maxLength(255),
                DatePicker::make('issued_at'),
                DatePicker::make('expires_at'),
                TextInput::make('document_path')
                    ->maxLength(255),
                Select::make('verification_status')
                    ->options(CertificationVerificationStatus::class)
                    ->default(CertificationVerificationStatus::Pending->value),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('issuer')
                    ->searchable(),
                TextColumn::make('pivot.verification_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (CertificationVerificationStatus $state): string => match ($state) {
                        CertificationVerificationStatus::Verified => 'success',
                        CertificationVerificationStatus::Pending => 'warning',
                        CertificationVerificationStatus::Rejected => 'danger',
                        CertificationVerificationStatus::Expired => 'gray',
                    }),
                TextColumn::make('pivot.issued_at')
                    ->label('Issued')
                    ->date()
                    ->sortable(),
                TextColumn::make('pivot.expires_at')
                    ->label('Expires')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name']),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
