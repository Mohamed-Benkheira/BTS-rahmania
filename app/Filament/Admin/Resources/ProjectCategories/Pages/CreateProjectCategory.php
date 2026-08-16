<?php

namespace App\Filament\Admin\Resources\ProjectCategories\Pages;

use App\Filament\Admin\Resources\ProjectCategories\ProjectCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProjectCategory extends CreateRecord
{
    protected static string $resource = ProjectCategoryResource::class;
}
