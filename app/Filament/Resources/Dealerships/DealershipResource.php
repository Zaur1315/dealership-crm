<?php

declare(strict_types=1);

namespace App\Filament\Resources\Dealerships;

use App\Filament\Resources\Dealerships\Pages\CreateDealership;
use App\Filament\Resources\Dealerships\Pages\EditDealership;
use App\Filament\Resources\Dealerships\Pages\ListDealerships;
use App\Filament\Resources\Dealerships\Schemas\DealershipForm;
use App\Filament\Resources\Dealerships\Tables\DealershipsTable;
use App\Models\Dealership;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DealershipResource extends Resource
{
    protected static ?string $model = Dealership::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Dealerships';

    protected static ?string $modelLabel = 'Dealership';

    protected static ?string $pluralModelLabel = 'Dealerships';

    protected static ?int $navigationSort = 30;

    protected static string|null|\UnitEnum $navigationGroup = 'Administration';

    public static function form(Schema $schema): Schema
    {
        return DealershipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DealershipsTable::configure($table);
    }

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

    public static function getPages(): array
    {
        return [
            'index' => ListDealerships::route('/'),
            'create' => CreateDealership::route('/create'),
            'edit' => EditDealership::route('/{record}/edit'),
        ];
    }
}
