<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads;

use App\Filament\Resources\Leads\Pages\CreateLead;
use App\Filament\Resources\Leads\Pages\EditLead;
use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Filament\Resources\Leads\Pages\ViewLead;
use App\Filament\Resources\Leads\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\Leads\RelationManagers\CommentsRelationManager;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

    public static function canView(Model $record): bool
    {
        return Auth::user() instanceof User;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $dealership = app(CurrentDealershipContext::class)->ensureSelected();

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
