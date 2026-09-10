<?php

namespace App\Filament\Admin\Resources\Projects\Pages;

use App\Filament\Admin\Resources\Projects\ProjectResource;
use App\Services\RecommendationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
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

        Notification::make()
            ->title('Recommendations Generated')
            ->body($message)
            ->success()
            ->send();
    }
}
