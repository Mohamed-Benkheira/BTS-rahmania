<?php

namespace App\Filament\Admin\Resources\AuditLogs\Tables;

use App\Filament\Admin\Resources\AuditLogs\AuditLogResource;
use App\Models\AuditLog;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '.deleted') || str_contains($state, '.rejected') || str_contains($state, '.cancelled') => 'danger',
                        str_contains($state, '.updated') || str_contains($state, '.requested') => 'warning',
                        str_contains($state, '.created') || str_contains($state, '.approved') || str_contains($state, '.activated') => 'success',
                        default => 'info',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('auditable_type')
                    ->label('Target Type')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('auditable_id')
                    ->label('Target ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (AuditLog $record): string => AuditLogResource::getUrl('view', ['record' => $record]))
            ->filters([
                SelectFilter::make('action')
                    ->options(fn () => AuditLog::query()->distinct()->whereNotNull('action')->pluck('action', 'action')->toArray())
                    ->searchable(),
                SelectFilter::make('auditable_type')
                    ->label('Target Type')
                    ->options(function (): array {
                        $types = AuditLog::query()->distinct()->whereNotNull('auditable_type')->pluck('auditable_type')->toArray();

                        return collect($types)->mapWithKeys(fn (string $type): array => [$type => class_basename($type)])->toArray();
                    }),
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
