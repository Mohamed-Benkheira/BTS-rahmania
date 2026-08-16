<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use App\Services\AuditService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    private array $oldRoleNames = [];

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->oldRoleNames = $this->record->getRoleNames()->sort()->values()->all();

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->refresh();

        $newRoleNames = $this->record->getRoleNames()->sort()->values()->all();

        if ($this->oldRoleNames !== $newRoleNames) {
            app(AuditService::class)->log(
                'user.roles.updated',
                $this->record,
                ['roles' => $this->oldRoleNames],
                ['roles' => $newRoleNames],
            );
        }
    }
}
