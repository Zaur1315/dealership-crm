<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class UserManagement extends Page
{
    protected string $view = 'filament.pages.user-management';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'User Management';

    protected static string|null|\UnitEnum $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 20;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isGm();
    }
}
