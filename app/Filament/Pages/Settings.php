<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Settings extends Page
{
    protected string $view = 'filament.pages.settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static string|null|\UnitEnum $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 40;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isGm();
    }
}
