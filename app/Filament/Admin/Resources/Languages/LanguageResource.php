<?php

namespace App\Filament\Admin\Resources\Languages;

use App\Filament\Admin\Resources\Languages\Pages\CreateLanguage;
use App\Filament\Admin\Resources\Languages\Pages\EditLanguage;
use App\Filament\Admin\Resources\Languages\Pages\ListLanguages;
use App\Filament\Admin\Resources\Languages\Schemas\LanguageForm;
use App\Filament\Admin\Resources\Languages\Tables\LanguagesTable;
use App\Models\Language;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Employees';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return LanguageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LanguagesTable::configure($table);
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
            'index' => ListLanguages::route('/'),
            'create' => CreateLanguage::route('/create'),
            'edit' => EditLanguage::route('/{record}/edit'),
        ];
    }
}
