<?php

namespace App\Filament\Admin\Resources\BusinessUnits\Pages;

use App\Filament\Admin\Resources\BusinessUnits\BusinessUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessUnit extends EditRecord
{
    protected static string $resource = BusinessUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
