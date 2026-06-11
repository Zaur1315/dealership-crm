<?php

declare(strict_types=1);

namespace App\Support\Dealership;

use App\Models\Dealership;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

final class CurrentDealershipContext
{
    private const SESSION_KEY = 'current_dealership_id';

    public function get(): ?Dealership
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return null;
        }

        $dealershipId = session(self::SESSION_KEY);

        if ($dealershipId === null) {
            return null;
        }

        $query = Dealership::query()
            ->whereKey($dealershipId)
            ->where('is_active', true);

        if (! $user->isGm()) {
            $query->whereHas('users', function ($query) use ($user): void {
                $query->where('users.id', $user->id);
            });
        }

        /** @var Dealership|null $dealership */
        $dealership = $query->first();

        return $dealership;
    }

    public function set(int $dealershipId): Dealership
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw new RuntimeException('Authenticated user is required.');
        }

        $query = Dealership::query()
            ->whereKey($dealershipId)
            ->where('is_active', true);

        if (! $user->isGm()) {
            $query->whereHas('users', function ($query) use ($user): void {
                $query->where('users.id', $user->id);
            });
        }

        /** @var Dealership|null $dealership */
        $dealership = $query->first();

        if (! $dealership instanceof Dealership) {
            throw new RuntimeException('Dealership is not available for current user.');
        }

        session([self::SESSION_KEY => $dealership->id]);

        return $dealership;
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
        if ($user->isGm()) {
            /** @var Collection<int, Dealership> $dealerships */
            $dealerships = Dealership::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return $dealerships;
        }

        /** @var Collection<int, Dealership> $dealerships */
        $dealerships = $user->dealerships()
            ->where('dealerships.is_active', true)
            ->orderBy('dealerships.name')
            ->get();

        return $dealerships;
    }

    public function ensureSelected(): Dealership
    {
        $dealership = $this->get();

        if (! $dealership instanceof Dealership) {
            throw new RuntimeException('Current dealership is not selected.');
        }

        return $dealership;
    }
}
