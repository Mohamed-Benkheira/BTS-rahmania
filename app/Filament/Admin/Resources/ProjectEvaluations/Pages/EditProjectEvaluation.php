<?php

namespace App\Filament\Admin\Resources\ProjectEvaluations\Pages;

use App\Filament\Admin\Resources\ProjectEvaluations\ProjectEvaluationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProjectEvaluation extends EditRecord
{
    protected static string $resource = ProjectEvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
