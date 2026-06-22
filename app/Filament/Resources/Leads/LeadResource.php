<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\Leads\Pages\CreateLead;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Filament\Resources\Leads\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\Leads\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\Leads\RelationManagers\EmailsRelationManager;
use App\Filament\Resources\Leads\RelationManagers\TasksRelationManager;
use App\Filament\Resources\Leads\Schemas\LeadForm;
use App\Filament\Resources\Leads\Schemas\LeadInfolist;
use App\Filament\Resources\Leads\Tables\LeadsTable;
use App\Models\Lead;
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

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Leads';

    protected static string|null|\UnitEnum $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'Lead';

    protected static ?string $pluralModelLabel = 'Leads';

    public static function form(Schema $schema): Schema
    {
        return LeadForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeadInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadsTable::configure($table);
    }

    /**
     * @throws CircularDependencyException
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public static function canView(Model $record): bool
    {
        return $record instanceof Lead && self::canAccessCurrentDealershipRecord($record);
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
            ->with(['dealership', 'createdBy']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeads::route('/'),
            'create' => CreateLead::route('/create'),
            'view' => ViewLead::route('/{record}'),
            'edit' => EditLead::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
            CommentsRelationManager::class,
            EmailsRelationManager::class,
            TasksRelationManager::class,
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
        return $record instanceof Lead && self::canAccessCurrentDealershipRecord($record);
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && $user->isManagerOrGm()
            && $record instanceof Lead
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
    private static function canAccessCurrentDealershipRecord(Lead $record): bool
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
