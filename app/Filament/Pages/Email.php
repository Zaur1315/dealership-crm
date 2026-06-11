<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Email extends Page
{
    protected string $view = 'filament.pages.email';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Email';

    protected static string|null|\UnitEnum $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 30;
}
