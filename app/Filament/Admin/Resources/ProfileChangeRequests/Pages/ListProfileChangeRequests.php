<?php

namespace App\Filament\Admin\Resources\ProfileChangeRequests\Pages;

use App\Filament\Admin\Resources\ProfileChangeRequests\ProfileChangeRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListProfileChangeRequests extends ListRecords
{
    protected static string $resource = ProfileChangeRequestResource::class;
}
