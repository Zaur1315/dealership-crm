<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Dealership;
use App\Models\User;
use App\Support\Dealership\CurrentDealershipContext;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DealershipSwitcher extends Component
{
    public ?int $selectedDealershipId = null;

    public function mount(CurrentDealershipContext $context): void
    {
        $dealership = $context->get();

        $this->selectedDealershipId = $dealership instanceof Dealership
            ? $dealership->id
            : null;
    }

    public function updatedSelectedDealershipId(?int $dealershipId): mixed
    {
        if ($dealershipId === null) {
            return null;
        }

        app(CurrentDealershipContext::class)->set($dealershipId);

        return redirect(request()->header('Referer') ?: route('filament.admin.pages.dashboard'));
    }

    public function render(): View
    {
        /** @var view-string $view */
        $view = 'livewire.dealership-switcher';

        return view($view, [
            'dealerships' => $this->dealerships(),
        ]);
    }

    /**
     * @return Collection<int, Dealership>
     */
    private function dealerships(): Collection
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return new Collection;
        }

        return app(CurrentDealershipContext::class)->availableFor($user);
    }
}
