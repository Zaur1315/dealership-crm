<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Tasks extends Page
{
    protected string $view = 'filament.pages.tasks';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Tasks';

    protected static string|null|\UnitEnum $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 20;
}
