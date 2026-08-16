<?php

namespace App\Filament\Admin\Resources\Positions;

use App\Filament\Admin\Resources\Positions\Pages\CreatePosition;
use App\Filament\Admin\Resources\Positions\Pages\EditPosition;
use App\Filament\Admin\Resources\Positions\Pages\ListPositions;
use App\Filament\Admin\Resources\Positions\Schemas\PositionForm;
use App\Filament\Admin\Resources\Positions\Tables\PositionsTable;
use App\Models\Position;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Organization';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return PositionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PositionsTable::configure($table);
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
            'index' => ListPositions::route('/'),
            'create' => CreatePosition::route('/create'),
            'edit' => EditPosition::route('/{record}/edit'),
        ];
    }
}
