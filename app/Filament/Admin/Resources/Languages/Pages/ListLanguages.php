<?php

namespace App\Filament\Admin\Resources\Languages\Pages;

use App\Filament\Admin\Resources\Languages\LanguageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLanguages extends ListRecords
{
    protected static string $resource = LanguageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
