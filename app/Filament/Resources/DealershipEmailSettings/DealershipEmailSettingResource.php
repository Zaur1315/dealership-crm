<?php

namespace App\Filament\Resources\DealershipEmailSettings;

use App\Filament\Resources\DealershipEmailSettings\Pages\CreateDealershipEmailSetting;
use App\Filament\Resources\DealershipEmailSettings\Pages\EditDealershipEmailSetting;
use App\Filament\Resources\DealershipEmailSettings\Pages\ListDealershipEmailSettings;
use App\Filament\Resources\DealershipEmailSettings\Schemas\DealershipEmailSettingForm;
use App\Models\User;
use App\Support\Dealership\CurrentDealershipContext;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DealershipEmailSettingResource extends Resource
{
    protected static ?string $navigationLabel = 'Email Settings';

    protected static ?string $modelLabel = 'Email Setting';

    protected static ?string $pluralModelLabel = 'Email Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 20;

    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isGm();
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isGm();
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isGm();
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isGm();
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = Auth::user();

        if (! $user instanceof User) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isGm()) {
            return $query->latest('id');
        }

        $dealership = app(CurrentDealershipContext::class)->ensureSelected();

        return $query
            ->where('dealership_id', $dealership->id)
            ->latest('id');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDealershipEmailSettings::route('/'),
            'create' => CreateDealershipEmailSetting::route('/create'),
            'edit' => EditDealershipEmailSetting::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return DealershipEmailSettingForm::configure($schema);
    }
}
