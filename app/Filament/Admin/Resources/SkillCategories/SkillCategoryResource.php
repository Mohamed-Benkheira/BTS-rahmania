<?php

namespace App\Filament\Admin\Resources\SkillCategories;

use App\Filament\Admin\Resources\SkillCategories\Pages\CreateSkillCategory;
use App\Filament\Admin\Resources\SkillCategories\Pages\EditSkillCategory;
use App\Filament\Admin\Resources\SkillCategories\Pages\ListSkillCategories;
use App\Filament\Admin\Resources\SkillCategories\Schemas\SkillCategoryForm;
use App\Filament\Admin\Resources\SkillCategories\Tables\SkillCategoriesTable;
use App\Models\SkillCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SkillCategoryResource extends Resource
{
    protected static ?string $model = SkillCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Employees';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SkillCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkillCategoriesTable::configure($table);
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
            'index' => ListSkillCategories::route('/'),
            'create' => CreateSkillCategory::route('/create'),
            'edit' => EditSkillCategory::route('/{record}/edit'),
        ];
    }
}
