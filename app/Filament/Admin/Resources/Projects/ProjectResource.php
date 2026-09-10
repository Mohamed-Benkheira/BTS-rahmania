<?php

namespace App\Filament\Admin\Resources\Projects;

use App\Filament\Admin\Resources\Projects\Pages\CreateProject;
use App\Filament\Admin\Resources\Projects\Pages\EditProject;
use App\Filament\Admin\Resources\Projects\Pages\ListProjects;
use App\Filament\Admin\Resources\Projects\RelationManagers\RecommendationsRelationManager;
use App\Filament\Admin\Resources\Projects\RelationManagers\RequiredCertificationsRelationManager;
use App\Filament\Admin\Resources\Projects\RelationManagers\RequiredLanguagesRelationManager;
use App\Filament\Admin\Resources\Projects\RelationManagers\RequiredSkillsRelationManager;
use App\Filament\Admin\Resources\Projects\RelationManagers\RequirementsRelationManager;
use App\Filament\Admin\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Admin\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationLabel = 'Projects';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RequirementsRelationManager::class,
            RequiredSkillsRelationManager::class,
            RequiredCertificationsRelationManager::class,
            RequiredLanguagesRelationManager::class,
            RecommendationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
