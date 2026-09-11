<?php

namespace App\Filament\Admin\Resources\ProfileChangeRequests\Pages;

use App\Enums\ProfileChangeStatus;
use App\Enums\ProfileChangeType;
use App\Filament\Admin\Resources\ProfileChangeRequests\ProfileChangeRequestResource;
use App\Services\ProfileChangeRequestService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewProfileChangeRequest extends ViewRecord
{
    protected static string $resource = ProfileChangeRequestResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('employee.full_name')
                            ->label('Employee'),
                        TextEntry::make('type')
                            ->label('Change type')
                            ->formatStateUsing(fn (ProfileChangeType $state): string => ucfirst($state->value)),
                        TextEntry::make('subjectDisplay')
                            ->label('Subject')
                            ->state(fn ($record) => $record->subjectDisplay()),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (ProfileChangeStatus $state): string => match ($state) {
                                ProfileChangeStatus::Pending => 'warning',
                                ProfileChangeStatus::Approved => 'success',
                                ProfileChangeStatus::Rejected => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('submitted_note')
                            ->label('Employee note')
                            ->placeholder('—'),
                        TextEntry::make('reviewer.name')
                            ->label('Reviewed by')
                            ->placeholder('—'),
                    ]),
                Section::make('Proposed changes')
                    ->schema([
                        KeyValueEntry::make('payload'),
                    ]),
                Section::make('Previous state')
                    ->schema([
                        KeyValueEntry::make('previous')
                            ->visible(fn (?array $state): bool => $state !== null),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record?->status === ProfileChangeStatus::Pending)
                ->form([
                    Textarea::make('reviewer_note')
                        ->label('Note (optional)')
                        ->rows(2)
                        ->maxLength(1000),
                ])
                ->modalSubmitActionLabel('Approve')
                ->action(function (array $data) {
                    app(ProfileChangeRequestService::class)->approve($this->record, auth()->user(), $data['reviewer_note'] ?? null);
                    Notification::make()->title('Change request approved')->success()->send();
                    $this->refreshFormData(['status', 'reviewer_id', 'reviewer_note', 'reviewed_at']);
                }),
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record?->status === ProfileChangeStatus::Pending)
                ->form([
                    Textarea::make('reviewer_note')
                        ->label('Reason (optional)')
                        ->rows(2)
                        ->maxLength(1000),
                ])
                ->modalSubmitActionLabel('Reject')
                ->action(function (array $data) {
                    app(ProfileChangeRequestService::class)->reject($this->record, auth()->user(), $data['reviewer_note'] ?? null);
                    Notification::make()->title('Change request rejected')->danger()->send();
                    $this->refreshFormData(['status', 'reviewer_id', 'reviewer_note', 'reviewed_at']);
                }),
        ];
    }
}
