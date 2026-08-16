<?php

namespace App\Filament\Admin\Resources\Roles\Pages;

use App\Filament\Admin\Resources\Roles\RoleResource;
use App\Services\AuditService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    private array $oldPermissions = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldPermissions = $this->record->permissions->pluck('name')->sort()->values()->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->refresh();

        $newPermissions = $this->record->permissions->pluck('name')->sort()->values()->all();

        if ($this->oldPermissions !== $newPermissions) {
            app(AuditService::class)->log(
                'role.updated',
                $this->record,
                ['permissions' => $this->oldPermissions],
                ['permissions' => $newPermissions],
            );
        }
    }
}
