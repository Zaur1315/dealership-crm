<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Dealership;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDealershipSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return $next($request);
        }

        if ($this->isBypassRoute($request)) {
            return $next($request);
        }

        $selectedDealershipId = session('selected_dealership_id');

        if ($selectedDealershipId !== null && $this->userCanAccessDealership($user, (int) $selectedDealershipId)) {
            return $next($request);
        }

        session()->forget('selected_dealership_id');

        $dealershipsQuery = Dealership::query()
            ->where('is_active', true);

        if (! $user->isGm()) {
            $dealershipsQuery->whereHas('users', fn ($query) => $query->whereKey($user->id));
        }

        $dealerships = $dealershipsQuery->get();

        if ($dealerships->count() === 1) {
            $dealership = $dealerships->first();

            if ($dealership instanceof Dealership) {
                session(['selected_dealership_id' => $dealership->id]);

                return $next($request);
            }
        }

        return redirect()->route('filament.admin.pages.select-dealership');
    }

    private function isBypassRoute(Request $request): bool
    {
        $route = $request->route();

        if ($route === null) {
            return false;
        }

        return in_array($route->getName(), [
            'filament.admin.pages.select-dealership',
            'filament.admin.auth.login',
            'filament.admin.auth.logout',
        ], true);
    }

    private function userCanAccessDealership(User $user, int $dealershipId): bool
    {
        if ($user->isGm()) {
            return Dealership::query()
                ->whereKey($dealershipId)
                ->where('is_active', true)
                ->exists();
        }

        return $user->dealerships()
            ->whereKey($dealershipId)
            ->where('is_active', true)
            ->exists();
    }
}
