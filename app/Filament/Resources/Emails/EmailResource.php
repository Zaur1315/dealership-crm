<?php

namespace App\Filament\Resources\Emails;

use App\Enums\EmailStatus;
use App\Filament\Resources\Emails\Pages\CreateEmail;
use App\Filament\Resources\Emails\Pages\ListEmails;
use App\Filament\Resources\Emails\Pages\ViewEmail;
use App\Filament\Resources\Emails\Schemas\EmailForm;
use App\Filament\Resources\Emails\Schemas\EmailInfolist;
use App\Filament\Resources\Emails\Tables\EmailsTable;
use App\Models\Email;
use App\Models\User;
use App\Support\Dealership\CurrentDealershipContext;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class EmailResource extends Resource
{
    protected static ?string $model = Email::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Email';

    protected static ?string $modelLabel = 'Email';

    protected static ?string $pluralModelLabel = 'Email';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return EmailForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmailInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
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
    public static function canView(Model $record): bool
    {
        return $record instanceof Email && self::canAccessCurrentDealershipRecord($record);
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

    public static function canEdit(Model $record): bool
    {
        return false;
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
            && $user->isGm()
            && $record instanceof Email
            && self::canAccessCurrentDealershipRecord($record);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmails::route('/'),
            'create' => CreateEmail::route('/create'),
            'view' => ViewEmail::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('status', '!=', EmailStatus::DELETED->value);

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
            ->latest('id');
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
    private static function canAccessCurrentDealershipRecord(Email $record): bool
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
