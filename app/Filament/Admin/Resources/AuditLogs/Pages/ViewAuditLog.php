<?php

namespace App\Filament\Admin\Resources\AuditLogs\Pages;

use App\Filament\Admin\Resources\AuditLogs\AuditLogResource;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Audit Event Metadata')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Log ID'),
                        TextEntry::make('created_at')
                            ->label('Timestamp')
                            ->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('action')
                            ->label('Action')
                            ->badge()
                            ->color(fn (string $state): string => match (true) {
                                str_contains($state, '.deleted') || str_contains($state, '.rejected') || str_contains($state, '.cancelled') => 'danger',
                                str_contains($state, '.updated') || str_contains($state, '.requested') => 'warning',
                                str_contains($state, '.created') || str_contains($state, '.approved') || str_contains($state, '.activated') => 'success',
                                default => 'info',
                            }),
                        TextEntry::make('user.name')
                            ->label('Actor')
                            ->placeholder('System'),
                        TextEntry::make('user.email')
                            ->label('Actor Email')
                            ->placeholder('—'),
                        TextEntry::make('ip_address')
                            ->label('IP Address')
                            ->placeholder('—'),
                        TextEntry::make('auditable_type')
                            ->label('Target Entity Type')
                            ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—'),
                        TextEntry::make('auditable_id')
                            ->label('Target Entity ID')
                            ->placeholder('—'),
                        TextEntry::make('user_agent')
                            ->label('User Agent')
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ]),
                Section::make('Payload Changes')
                    ->columns(2)
                    ->schema([
                        KeyValueEntry::make('old_values')
                            ->label('Previous State (Before)')
                            ->placeholder('No previous data recorded'),
                        KeyValueEntry::make('new_values')
                            ->label('New State (After)')
                            ->placeholder('No new data recorded'),
                    ]),
            ]);
    }
}
