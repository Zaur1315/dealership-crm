<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Leads extends Page
{
    protected string $view = 'filament.pages.leads';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Leads';

    protected static string|null|\UnitEnum $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 10;
}
