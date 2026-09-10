<?php

namespace App\Filament\Admin\Resources\Projects\Pages;

use App\Filament\Admin\Resources\Projects\ProjectResource;
use App\Services\AssignmentService;
use App\Services\RecommendationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_assignment')
                ->label('Create Assignment')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Start date')
                        ->default(fn () => $this->record->start_date)
                        ->native(false),
                    DatePicker::make('end_date')
                        ->label('End date')
                        ->default(fn () => $this->record->target_end_date)
                        ->native(false),
                    Textarea::make('notes')
                        ->rows(2)
                        ->maxLength(1024),
                ])
                ->modalHeading(fn () => "Create Assignment for [{$this->record->name}]")
                ->modalSubmitActionLabel('Create Assignment')
                ->action(fn (array $data) => $this->createAssignment($data))
                ->visible(fn () => auth()->user()->can('create assignments')),
            Action::make('recommend')
                ->label('Recommend')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Run Recommendation Engine')
                ->modalDescription(fn () => "Analyze eligible candidates for [{$this->record->name}] and generate ranked recommendations ({$this->record->assignment_mode->value} mode).")
                ->modalSubmitActionLabel('Run Recommendation')
                ->action(fn () => $this->runRecommendation())
                ->visible(fn () => auth()->user()->can('view recommendations')),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    private function createAssignment(array $data): void
    {
        $service = app(AssignmentService::class)->actor(auth()->user());

        try {
            $assignment = $service->create($this->record, [
                'start_date' => $data['start_date'] ?? $this->record->start_date,
                'end_date' => $data['end_date'] ?? $this->record->target_end_date,
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (\DomainException $e) {
            Notification::make()
                ->danger()
                ->title('Cannot create assignment')
                ->body($e->getMessage())
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title('Assignment Created')
            ->body("Assignment #{$assignment->id} for [{$this->record->name}] is pending approval.")
            ->send();
    }

    private function runRecommendation(): void
    {
        $project = $this->record;
        $user = auth()->user();

        /** @var RecommendationService $service */
        $service = app(RecommendationService::class);
        $run = $service->run($project, $user);

        $count = $run->recommendations()->count();
        $mode = $project->assignment_mode->value;
        $top = $run->recommendations()
            ->with(['employee', 'team', 'department'])
            ->orderBy('rank')
            ->first();

        $targetName = match (true) {
            $top?->employee => $top->employee->full_name,
            $top?->team => $top->team->name,
            $top?->department => $top->department->name,
            default => null,
        };

        $message = "{$count} recommendation(s) generated for {$mode} mode.";

        if ($targetName) {
            $message .= " Top pick: {$targetName} (score: ".number_format($top->total_score * 100, 1).'%).';
        }

        if ($count === 0) {
            $blockers = $run->blockers ?? ['No candidate meets every mandatory requirement.'];

            $notification = Notification::make()
                ->title('No Candidates Found')
                ->body('No candidate met the mandatory requirements for this project.'.PHP_EOL.'• '.implode(PHP_EOL.'• ', $blockers))
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        Notification::make()
            ->title('Recommendations Generated')
            ->body($message)
            ->success()
            ->send();
    }
}
