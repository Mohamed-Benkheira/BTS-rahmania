<?php

namespace App\Filament\Admin\Resources\Employees;

use App\Filament\Admin\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Admin\Resources\Employees\Pages\EditEmployee;
use App\Filament\Admin\Resources\Employees\Pages\ListEmployees;
use App\Filament\Admin\Resources\Employees\RelationManagers\AvailabilitiesRelationManager;
use App\Filament\Admin\Resources\Employees\RelationManagers\CertificationsRelationManager;
use App\Filament\Admin\Resources\Employees\RelationManagers\LanguagesRelationManager;
use App\Filament\Admin\Resources\Employees\RelationManagers\SkillsRelationManager;
use App\Filament\Admin\Resources\Employees\RelationManagers\WorkloadsRelationManager;
use App\Filament\Admin\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Admin\Resources\Employees\Tables\EmployeesTable;
use App\Models\Employee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Employees';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'first_name';

    protected static ?string $navigationLabel = 'Employees';

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SkillsRelationManager::class,
            CertificationsRelationManager::class,
            LanguagesRelationManager::class,
            AvailabilitiesRelationManager::class,
            WorkloadsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
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
