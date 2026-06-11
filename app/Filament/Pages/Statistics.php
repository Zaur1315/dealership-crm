<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Statistics extends Page
{
    protected string $view = 'filament.pages.statistics';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Statistics';

    protected static string|null|\UnitEnum $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && ($user->isGm() || $user->isManager());
    }
}
