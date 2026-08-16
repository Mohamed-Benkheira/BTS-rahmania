<?php

namespace App\Filament\Admin\Resources\Roles\Pages;

use App\Filament\Admin\Resources\Roles\RoleResource;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        $this->record->refresh();

        app(AuditService::class)->log(
            'role.updated',
            $this->record,
            null,
            [
                'name' => $this->record->name,
                'permissions' => $this->record->permissions->pluck('name')->all(),
            ],
        );
    }
}
