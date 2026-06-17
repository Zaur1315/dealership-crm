<?php

declare(strict_types=1);

namespace App\Filament\Resources\DealershipEmailSettings;

use App\Filament\Resources\DealershipEmailSettings\Pages\CreateDealershipEmailSetting;
use App\Filament\Resources\DealershipEmailSettings\Pages\EditDealershipEmailSetting;
use App\Filament\Resources\DealershipEmailSettings\Pages\ListDealershipEmailSettings;
use App\Filament\Resources\DealershipEmailSettings\Schemas\DealershipEmailSettingForm;
use App\Filament\Resources\DealershipEmailSettings\Tables\DealershipEmailSettingsTable;
use App\Models\DealershipEmailSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DealershipEmailSettingResource extends Resource
{
    protected static ?string $model = DealershipEmailSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Email Settings';

    protected static ?string $modelLabel = 'Email Setting';

    protected static ?string $pluralModelLabel = 'Email Settings';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return DealershipEmailSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DealershipEmailSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDealershipEmailSettings::route('/'),
            'create' => CreateDealershipEmailSetting::route('/create'),
            'edit' => EditDealershipEmailSetting::route('/{record}/edit'),
        ];
    }
}
