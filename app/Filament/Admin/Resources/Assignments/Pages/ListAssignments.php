<?php

namespace App\Filament\Admin\Resources\Assignments\Pages;

use App\Filament\Admin\Resources\Assignments\AssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssignments extends ListRecords
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
