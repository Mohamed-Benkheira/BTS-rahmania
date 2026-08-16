<?php

namespace App\Filament\Admin\Resources\ProjectCategories\Pages;

use App\Filament\Admin\Resources\ProjectCategories\ProjectCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjectCategories extends ListRecords
{
    protected static string $resource = ProjectCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
