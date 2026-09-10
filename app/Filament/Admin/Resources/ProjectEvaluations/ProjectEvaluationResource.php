<?php

namespace App\Filament\Admin\Resources\ProjectEvaluations;

use App\Filament\Admin\Resources\ProjectEvaluations\Pages\CreateProjectEvaluation;
use App\Filament\Admin\Resources\ProjectEvaluations\Pages\EditProjectEvaluation;
use App\Filament\Admin\Resources\ProjectEvaluations\Pages\ListProjectEvaluations;
use App\Filament\Admin\Resources\ProjectEvaluations\Schemas\ProjectEvaluationForm;
use App\Filament\Admin\Resources\ProjectEvaluations\Tables\ProjectEvaluationsTable;
use App\Models\ProjectEvaluation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProjectEvaluationResource extends Resource
{
    protected static ?string $model = ProjectEvaluation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $navigationLabel = 'Project Evaluations';

    protected static ?string $recordTitleAttribute = 'id';

    protected static UnitEnum|string|null $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return ProjectEvaluationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectEvaluationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjectEvaluations::route('/'),
            'create' => CreateProjectEvaluation::route('/create'),
            'edit' => EditProjectEvaluation::route('/{record}/edit'),
        ];
    }
}
