<?php

namespace App\Filament\Admin\Resources\ProjectEvaluations\Pages;

use App\Filament\Admin\Resources\ProjectEvaluations\ProjectEvaluationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjectEvaluations extends ListRecords
{
    protected static string $resource = ProjectEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
