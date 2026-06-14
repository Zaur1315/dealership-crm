<?php

declare(strict_types=1);

namespace App\Filament\Resources\Tasks;

use App\Filament\Resources\Tasks\Pages\CreateTask;
use App\Filament\Resources\Tasks\Pages\EditTask;
use App\Filament\Resources\Tasks\Pages\ListTasks;
use App\Filament\Resources\Tasks\Pages\ViewTask;
use App\Filament\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Resources\Tasks\Schemas\TaskInfolist;
use App\Filament\Resources\Tasks\Tables\TasksTable;
use App\Models\Task;
use App\Models\User;
use App\Support\Dealership\CurrentDealershipContext;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Tasks';

    protected static string|null|\UnitEnum $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'Task';

    protected static ?string $pluralModelLabel = 'Tasks';

    public static function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TaskInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
    }

    public static function canView(Model $record): bool
    {
        return Auth::user() instanceof User;
    }

    public static function getEloquentQuery(): Builder
    {
        $dealership = app(CurrentDealershipContext::class)->ensureSelected();

        return parent::getEloquentQuery()
            ->where('dealership_id', $dealership->id)
            ->with(['lead', 'createdBy', 'completedBy']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'view' => ViewTask::route('/{record}'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return Auth::user() instanceof User;
    }

    public static function canCreate(): bool
    {
        return Auth::user() instanceof User;
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::user() instanceof User;
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isManagerOrGm();
    }
}
