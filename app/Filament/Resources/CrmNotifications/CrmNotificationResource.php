<?php

namespace App\Filament\Resources\CrmNotifications;

use App\Filament\Resources\CrmNotifications\Pages\ListCrmNotifications;
use App\Filament\Resources\CrmNotifications\Pages\ViewCrmNotification;
use App\Filament\Resources\CrmNotifications\Schemas\CrmNotificationForm;
use App\Filament\Resources\CrmNotifications\Schemas\CrmNotificationInfolist;
use App\Filament\Resources\CrmNotifications\Tables\CrmNotificationsTable;
use App\Models\CrmNotification;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CrmNotificationResource extends Resource
{
    protected static ?string $model = CrmNotification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return CrmNotificationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CrmNotificationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrmNotificationsTable::configure($table);
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
            'index' => ListCrmNotifications::route('/'),
            'view' => ViewCrmNotification::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('recipient_user_id', Auth::id())
            ->latest('id');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = CrmNotification::query()
            ->where('recipient_user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canViewAny(): bool
    {
        return Auth::user() instanceof User;
    }
}
