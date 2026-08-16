<?php

namespace App\Filament\Admin\Resources\ProjectCategories\Pages;

use App\Filament\Admin\Resources\ProjectCategories\ProjectCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProjectCategory extends EditRecord
{
    protected static string $resource = ProjectCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
