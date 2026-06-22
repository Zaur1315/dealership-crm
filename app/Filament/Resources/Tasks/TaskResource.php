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
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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

    /**
     * @throws CircularDependencyException
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function canView(Model $record): bool
    {
        return $record instanceof Task && self::canAccessCurrentDealershipRecord($record);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        if (! $user instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        $context = app(CurrentDealershipContext::class);
        $dealership = $context->ensureSelected();

        if (! $context->availableFor($user)->contains('id', $dealership->id)) {
            return $query->whereRaw('1 = 0');
        }

        return $query
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

    /**
     * @throws CircularDependencyException
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function canCreate(): bool
    {
        return self::hasCurrentDealershipAccess();
    }

    /**
     * @throws CircularDependencyException
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function canEdit(Model $record): bool
    {
        return $record instanceof Task && self::canAccessCurrentDealershipRecord($record);
    }

    /**
     * @throws CircularDependencyException
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && $user->isManagerOrGm()
            && $record instanceof Task
            && self::canAccessCurrentDealershipRecord($record);
    }

    /**
     * @throws CircularDependencyException
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private static function hasCurrentDealershipAccess(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        $context = app(CurrentDealershipContext::class);
        $dealership = $context->get();

        return $dealership !== null
            && $context->availableFor($user)->contains('id', $dealership->id);
    }

    /**
     * @throws CircularDependencyException
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    private static function canAccessCurrentDealershipRecord(Task $record): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        $context = app(CurrentDealershipContext::class);
        $dealership = $context->get();

        return $dealership !== null
            && $record->dealership_id === $dealership->id
            && $context->availableFor($user)->contains('id', $record->dealership_id);
    }
}
