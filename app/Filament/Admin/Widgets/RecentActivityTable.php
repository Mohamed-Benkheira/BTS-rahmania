<?php

namespace App\Filament\Admin\Widgets;

use App\Models\AuditLog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivityTable extends TableWidget
{
    protected int|string|array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Activity')
            ->description('Latest assignment and audit events.')
            ->query(
                AuditLog::query()
                    ->with('user')
                    ->orderByDesc('created_at')
                    ->limit(10),
            )
            ->columns([
                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->color(fn (string $state): string => $this->actionColor($state))
                    ->formatStateUsing(fn (string $state): string => $this->actionLabel($state))
                    ->tooltip(fn (string $state): string => $this->actionTooltip($state)),
                TextColumn::make('details')
                    ->label('Details')
                    ->getStateUsing(fn (AuditLog $record): string => $this->details($record))
                    ->limit(40)
                    ->tooltip(fn (AuditLog $record): string => $this->details($record)),
                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('System'),
                TextColumn::make('created_at')
                    ->label('When')
                    ->since()
                    ->tooltip(fn (AuditLog $record): string => $record->created_at->format('Y-m-d H:i:s')),
            ])
            ->paginated(false);
    }

    private function actionColor(string $action): string
    {
        return match (true) {
            str_contains($action, '.deleted') => 'danger',
            str_contains($action, '.updated') => 'warning',
            str_contains($action, 'assignment.approved'),
            str_contains($action, 'assignment.activated') => 'success',
            default => 'info',
        };
    }

    private function actionLabel(string $action): string
    {
        return match ($action) {
            'assignment.created' => 'Created',
            'assignment.approved' => 'Approved',
            'assignment.activated' => 'Activated',
            'assignment.completed' => 'Completed',
            'assignment.rejected' => 'Rejected',
            'assignment.cancelled' => 'Cancelled',
            'employee.created' => 'Employee created',
            'employee.updated' => 'Employee updated',
            'employee.deleted' => 'Employee deleted',
            'project.created' => 'Project created',
            'project.updated' => 'Project updated',
            'project.deleted' => 'Project deleted',
            'role.updated' => 'Role updated',
            'user.roles.updated' => 'Roles updated',
            default => ucfirst(str_replace('_', ' ', $action)),
        };
    }

    private function actionTooltip(string $action): string
    {
        return match ($action) {
            'assignment.created' => 'A new assignment was created for a project.',
            'assignment.approved' => 'The assignment was approved.',
            'assignment.activated' => 'The assignment became active.',
            'assignment.completed' => 'The assignment was marked as completed.',
            'assignment.rejected' => 'The assignment was rejected.',
            'assignment.cancelled' => 'The assignment was cancelled.',
            'employee.created' => 'An employee profile was created.',
            'employee.updated' => 'An employee profile was updated.',
            'employee.deleted' => 'An employee profile was removed.',
            'project.created' => 'A project was created.',
            'project.updated' => 'A project was updated.',
            'project.deleted' => 'A project was removed.',
            'role.updated' => 'A role or its permissions were changed.',
            'user.roles.updated' => 'A user\'s roles were changed.',
            default => 'Audit event recorded in the system.',
        };
    }

    private function details(AuditLog $record): string
    {
        $newValues = $record->new_values;
        $oldValues = $record->old_values;

        if (is_array($newValues) && isset($newValues['status'])) {
            return 'Status set to '.ucfirst(str_replace('_', ' ', $newValues['status']));
        }

        if ($record->action === 'assignment.created' && is_array($newValues) && isset($newValues['id'])) {
            return 'Assignment #'.$newValues['id'];
        }

        $entityName = $this->entityName($record->action, $newValues) ?? $this->entityName($record->action, $oldValues);

        if ($entityName !== null) {
            return $entityName;
        }

        return str_replace('_', ' ', ucfirst($record->action));
    }

    /**
     * @param  array<string, mixed>|null  $values
     */
    private function entityName(string $action, ?array $values): ?string
    {
        if (! is_array($values)) {
            return null;
        }

        if (str_starts_with($action, 'employee.')) {
            $name = trim(($values['first_name'] ?? '').' '.($values['last_name'] ?? ''));

            return $name !== '' ? $name : null;
        }

        if (str_starts_with($action, 'project.')) {
            return $values['name'] ?? null;
        }

        if (str_starts_with($action, 'role.') || str_starts_with($action, 'user.')) {
            return $values['name'] ?? $values['email'] ?? null;
        }

        return null;
    }
}
