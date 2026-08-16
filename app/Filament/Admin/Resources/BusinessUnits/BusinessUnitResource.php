<?php

namespace App\Filament\Admin\Resources\BusinessUnits;

use App\Filament\Admin\Resources\BusinessUnits\Pages\CreateBusinessUnit;
use App\Filament\Admin\Resources\BusinessUnits\Pages\EditBusinessUnit;
use App\Filament\Admin\Resources\BusinessUnits\Pages\ListBusinessUnits;
use App\Filament\Admin\Resources\BusinessUnits\Schemas\BusinessUnitForm;
use App\Filament\Admin\Resources\BusinessUnits\Tables\BusinessUnitsTable;
use App\Models\BusinessUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BusinessUnitResource extends Resource
{
    protected static ?string $model = BusinessUnit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Organization';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return BusinessUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBusinessUnits::route('/'),
            'create' => CreateBusinessUnit::route('/create'),
            'edit' => EditBusinessUnit::route('/{record}/edit'),
        ];
    }
}
