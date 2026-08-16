<?php

namespace App\Filament\Admin\Resources\Assignments;

use App\Filament\Admin\Resources\Assignments\Pages\CreateAssignment;
use App\Filament\Admin\Resources\Assignments\Pages\EditAssignment;
use App\Filament\Admin\Resources\Assignments\Pages\ListAssignments;
use App\Filament\Admin\Resources\Assignments\RelationManagers\MembersRelationManager;
use App\Filament\Admin\Resources\Assignments\RelationManagers\StatusHistoryRelationManager;
use App\Filament\Admin\Resources\Assignments\RelationManagers\TeamsRelationManager;
use App\Filament\Admin\Resources\Assignments\Schemas\AssignmentForm;
use App\Filament\Admin\Resources\Assignments\Tables\AssignmentsTable;
use App\Models\Assignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Assignments';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|UnitEnum|null $navigationGroup = 'Projects';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return AssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
            TeamsRelationManager::class,
            StatusHistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAssignments::route('/'),
            'create' => CreateAssignment::route('/create'),
            'edit' => EditAssignment::route('/{record}/edit'),
        ];
    }
}
