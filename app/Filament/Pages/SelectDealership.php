<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Dealership;
use App\Models\User;
use App\Support\Dealership\CurrentDealershipContext;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class SelectDealership extends Page
{
    protected string $view = 'filament.pages.select-dealership';

    protected static bool $shouldRegisterNavigation = false;

    /**
     * @var Collection<int, Dealership>
     */
    public Collection $dealerships;

    public function mount(CurrentDealershipContext $context): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        $this->dealerships = $context->availableFor($user);

        if ($user->isSalesperson() && $this->dealerships->count() === 1) {
            $context->set((int) $this->dealerships->first()->id);

            $this->redirect('/admin');
        }
    }

    public function selectDealership(int $dealershipId, CurrentDealershipContext $context): void
    {
        $context->set($dealershipId);

        $this->redirect('/admin');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('logout')
                ->label('Logout')
                ->url('/admin/logout'),
        ];
    }
}
