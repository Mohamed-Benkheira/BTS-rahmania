<?php

namespace App\Filament\Admin\Resources\ProjectCategories;

use App\Filament\Admin\Resources\ProjectCategories\Pages\CreateProjectCategory;
use App\Filament\Admin\Resources\ProjectCategories\Pages\EditProjectCategory;
use App\Filament\Admin\Resources\ProjectCategories\Pages\ListProjectCategories;
use App\Filament\Admin\Resources\ProjectCategories\Schemas\ProjectCategoryForm;
use App\Filament\Admin\Resources\ProjectCategories\Tables\ProjectCategoriesTable;
use App\Models\ProjectCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProjectCategoryResource extends Resource
{
    protected static ?string $model = ProjectCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolderOpen;

    protected static ?string $navigationLabel = 'Project Categories';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProjectCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectCategoriesTable::configure($table);
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
            'index' => ListProjectCategories::route('/'),
            'create' => CreateProjectCategory::route('/create'),
            'edit' => EditProjectCategory::route('/{record}/edit'),
        ];
    }
}
