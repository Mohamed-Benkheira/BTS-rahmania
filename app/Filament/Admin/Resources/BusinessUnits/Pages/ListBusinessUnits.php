<?php

namespace App\Filament\Admin\Resources\BusinessUnits\Pages;

use App\Filament\Admin\Resources\BusinessUnits\BusinessUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBusinessUnits extends ListRecords
{
    protected static string $resource = BusinessUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
