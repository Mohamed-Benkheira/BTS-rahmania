<?php

namespace App\Filament\Admin\Resources\ProfileChangeRequests;

use App\Filament\Admin\Resources\ProfileChangeRequests\Pages\ListProfileChangeRequests;
use App\Filament\Admin\Resources\ProfileChangeRequests\Pages\ViewProfileChangeRequest;
use App\Filament\Admin\Resources\ProfileChangeRequests\Tables\ProfileChangeRequestsTable;
use App\Models\ProfileChangeRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProfileChangeRequestResource extends Resource
{
    protected static ?string $model = ProfileChangeRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Profile Requests';

    protected static string|UnitEnum|null $navigationGroup = 'Employees';

    protected static ?int $navigationSort = 6;

    public static function table(Table $table): Table
    {
        return ProfileChangeRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProfileChangeRequests::route('/'),
            'view' => ViewProfileChangeRequest::route('/{record}'),
        ];
    }
}
