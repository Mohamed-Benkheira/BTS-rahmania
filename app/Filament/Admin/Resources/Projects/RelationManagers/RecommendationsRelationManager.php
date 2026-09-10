<?php

namespace App\Filament\Admin\Resources\Projects\RelationManagers;

use App\Enums\EmploymentStatus;
use App\Models\Recommendation;
use App\Models\RecommendationRun;
use App\Services\AssignmentService;
use App\Services\AuditService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecommendationsRelationManager extends RelationManager
{
    protected static string $relationship = 'recommendationRuns';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('Run #')
                    ->sortable()
                    ->badge(),
                TextColumn::make('executor.name')
                    ->label('Run by')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('algorithm_version')
                    ->label('Algorithm')
                    ->sortable(),
                TextColumn::make('criteria_snapshot.assignment_mode')
                    ->label('Mode')
                    ->formatStateUsing(fn ($state) => str_replace('_', ' ', ucfirst($state ?? '')))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'team' => Color::Blue,
                        'department' => Color::Purple,
                        default => Color::Green,
                    }),
                TextColumn::make('recommendations_count')
                    ->label('Candidates')
                    ->counts('recommendations')
                    ->sortable(),
                TextColumn::make('executed_at')
                    ->label('Executed')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                Action::make('view_recommendations')
                    ->label('View Rankings')
                    ->icon('heroicon-o-list-bullet')
                    ->color(Color::Blue)
                    ->modalHeading(fn ($record) => "Recommendation Run #{$record->id} (".($record->criteria_snapshot['assignment_mode'] ?? 'employee').' mode)')
                    ->modalContent(fn ($record) => view('filament.admin.recommendations-modal', [
                        'recommendations' => $record->recommendations()
                            ->with(['employee', 'team', 'department'])
                            ->orderBy('rank')
                            ->get(),
                    ]))
                    ->modalSubmitAction(false),
                Action::make('create_assignment_from_top_match')
                    ->label('Create Assignment from Top Match')
                    ->icon('heroicon-o-paper-airplane')
                    ->color(Color::Green)
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $this->assignmentModalHeading($record))
                    ->modalDescription(fn ($record) => $this->assignmentModalDescription($record))
                    ->modalSubmitActionLabel('Create Assignment')
                    ->action(fn (RecommendationRun $record) => $this->createAssignmentFromTopMatch($record))
                    ->visible(fn () => auth()->user()->can('create assignments')),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                ActionGroup::make([]),
            ]);
    }

    private function assignmentModalHeading(RecommendationRun $run): string
    {
        $top = $run->recommendations()->orderBy('rank')->first();

        if ($top === null) {
            return 'No ranked recommendations';
        }

        return 'Create Assignment from Top Match';
    }

    private function assignmentModalDescription(RecommendationRun $run): string
    {
        $project = $this->getOwnerRecord();
        $top = $run->recommendations()
            ->with(['employee', 'team', 'department'])
            ->orderBy('rank')
            ->first();

        $target = match (true) {
            $top?->employee !== null => "employee {$top->employee->full_name}",
            $top?->team !== null => "team {$top->team->name}",
            $top?->department !== null => "department {$top->department->name}",
            default => 'the top-ranked candidate',
        };

        if ($project->assignments()->whereIn('status', ['approved', 'active'])->exists()) {
            return "{$project->name} already has an approved or active assignment. A new assignment cannot be created until it completes.";
        }

        return "Approves the top-ranked recommendation (rank #1: {$target}) for [{$project->name}], creates a new pending assignment, and links the recommended candidate(s).";
    }

    private function createAssignmentFromTopMatch(RecommendationRun $run): void
    {
        $project = $this->getOwnerRecord();
        $user = auth()->user();

        if ($project->assignments()->whereIn('status', ['approved', 'active'])->exists()) {
            Notification::make()
                ->danger()
                ->title('Assignment blocked')
                ->body("[{$project->name}] already has an approved or active assignment.")
                ->send();

            return;
        }

        /** @var Recommendation|null $top */
        $top = $run->recommendations()
            ->with(['employee', 'team', 'department'])
            ->orderBy('rank')
            ->first();

        if ($top === null) {
            Notification::make()
                ->warning()
                ->title('No recommendations')
                ->body('This run produced no ranked recommendations.')
                ->send();

            return;
        }

        try {
            $assignment = app(AssignmentService::class)
                ->actor($user)
                ->create($project);
        } catch (\DomainException $e) {
            Notification::make()
                ->danger()
                ->title('Assignment blocked')
                ->body($e->getMessage())
                ->send();

            return;
        }

        $service = app(AssignmentService::class)->actor($user);
        $members = [];

        if ($top->employee_id !== null) {
            $service->assignMembers($assignment, [$top->employee_id]);
        } elseif ($top->team_id !== null) {
            $service->assignTeams($assignment, [$top->team_id]);
        } elseif ($top->department_id !== null && $top->department !== null) {
            $members = $top->department->employees()
                ->where('employment_status', EmploymentStatus::Active->value)
                ->pluck('id')
                ->all();

            if ($members !== []) {
                $service->assignMembers($assignment, $members);
            }
        }

        $top->forceFill(['status' => 'approved'])->save();

        app(AuditService::class)->log(
            'recommendation.approved',
            $top,
            ['status' => 'pending'],
            ['status' => 'approved', 'assignment_id' => $assignment->id, 'reason' => 'assignment created from top match'],
            $user,
        );

        Notification::make()
            ->success()
            ->title('Assignment Created')
            ->body("Assignment #{$assignment->id} created from the top-ranked recommendation and is pending approval.")
            ->send();
    }
}
