<?php

declare(strict_types=1);

namespace App\Support\Dealership;

use App\Models\Dealership;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class CurrentDealershipContext
{
    private const SESSION_KEY = 'selected_dealership_id';

    public function get(): ?Dealership
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        $selectedDealershipId = session(self::SESSION_KEY);

        if ($selectedDealershipId !== null) {
            $dealership = $this->findAccessibleDealership($user, (int) $selectedDealershipId);

            if ($dealership instanceof Dealership) {
                return $dealership;
            }

            $this->clear();
        }

        $availableDealerships = $this->availableFor($user);

        if ($availableDealerships->count() === 1) {
            $dealership = $availableDealerships->first();

            if ($dealership instanceof Dealership) {
                $this->set($dealership->id);

                return $dealership;
            }
        }

        return null;
    }

    public function ensureSelected(): Dealership
    {
        $dealership = $this->get();

        if (! $dealership instanceof Dealership) {
            throw new RuntimeException('Current dealership is not selected.');
        }

        return $dealership;
    }

    public function set(int $dealershipId): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw new RuntimeException('Authenticated user is required to select dealership.');
        }

        $dealership = $this->findAccessibleDealership($user, $dealershipId);

        if (! $dealership instanceof Dealership) {
            throw new RuntimeException('Selected dealership is not available for current user.');
        }

        session([self::SESSION_KEY => $dealership->id]);
    }

    public function select(Dealership $dealership): void
    {
        $this->set($dealership->id);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * @return Collection<int, Dealership>
     */
    public function availableFor(User $user): Collection
    {
        $query = Dealership::query()
            ->where('is_active', true)
            ->orderBy('name');

        if (! $user->isGm()) {
            $query->whereHas('users', fn ($query) => $query->whereKey($user->id));
        }

        return $query->get();
    }

    private function findAccessibleDealership(User $user, int $dealershipId): ?Dealership
    {
        $query = Dealership::query()
            ->whereKey($dealershipId)
            ->where('is_active', true);

        if (! $user->isGm()) {
            $query->whereHas('users', fn ($query) => $query->whereKey($user->id));
        }

        return $query->first();
    }
}
