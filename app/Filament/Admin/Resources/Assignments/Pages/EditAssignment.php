<?php

namespace App\Filament\Admin\Resources\Assignments\Pages;

use App\Enums\AssignmentStatus;
use App\Filament\Admin\Resources\Assignments\AssignmentResource;
use App\Models\Assignment;
use App\Services\AssignmentService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        $service = app(AssignmentService::class)->actor(auth()->user());
        $assignment = $this->assignment();

        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $assignment->status === AssignmentStatus::Pending)
                ->requiresConfirmation()
                ->action(function () use ($service, $assignment) {
                    $service->approve($assignment);
                    Notification::make()->title('Assignment approved')->success()->send();
                    $this->refreshFormData(['status', 'approved_at', 'approved_by']);
                }),
            Action::make('activate')
                ->label('Activate')
                ->icon('heroicon-o-play-circle')
                ->color('info')
                ->visible(fn () => $assignment->status === AssignmentStatus::Approved)
                ->requiresConfirmation()
                ->action(function () use ($service, $assignment) {
                    $service->activate($assignment);
                    Notification::make()->title('Assignment activated')->success()->send();
                    $this->refreshFormData(['status']);
                }),
            Action::make('complete')
                ->label('Complete')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->visible(fn () => $assignment->status === AssignmentStatus::Active)
                ->requiresConfirmation()
                ->action(function () use ($service, $assignment) {
                    $service->complete($assignment);
                    Notification::make()->title('Assignment completed')->success()->send();
                    $this->refreshFormData(['status']);
                }),
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $assignment->status === AssignmentStatus::Pending)
                ->requiresConfirmation()
                ->action(function () use ($service, $assignment) {
                    $service->reject($assignment);
                    Notification::make()->title('Assignment rejected')->danger()->send();
                    $this->refreshFormData(['status']);
                }),
            Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-bolt-slash')
                ->color('danger')
                ->visible(fn () => in_array($assignment->status, [AssignmentStatus::Pending, AssignmentStatus::Approved, AssignmentStatus::Active], true))
                ->requiresConfirmation()
                ->action(function () use ($service, $assignment) {
                    $service->cancel($assignment);
                    Notification::make()->title('Assignment cancelled')->warning()->send();
                    $this->refreshFormData(['status']);
                }),
            DeleteAction::make(),
        ];
    }

    protected function assignment(): Assignment
    {
        $record = $this->record;

        if ($record instanceof Assignment) {
            return $record;
        }

        if (is_int($record) || is_string($record)) {
            return Assignment::findOrFail($record);
        }

        throw new \LogicException('Assignment record not found.');
    }
}
