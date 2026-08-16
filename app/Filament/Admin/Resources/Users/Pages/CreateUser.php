<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use App\Services\AuditService;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->record->refresh();

        app(AuditService::class)->log(
            'user.created',
            $this->record,
            null,
            [
                'name' => $this->record->name,
                'email' => $this->record->email,
                'roles' => $this->record->getRoleNames()->all(),
            ],
        );
    }
}
