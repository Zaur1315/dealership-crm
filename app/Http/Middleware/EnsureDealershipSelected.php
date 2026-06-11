<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Dealership\CurrentDealershipContext;
use Closure;
use Illuminate\Container\EntryNotFoundException;
use Illuminate\Contracts\Container\CircularDependencyException;
use Illuminate\Http\Request;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class EnsureDealershipSelected
{
    /**
     * @throws CircularDependencyException
     * @throws EntryNotFoundException
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin/login') || $request->is('admin/logout') || $request->is('admin/select-dealership')) {
            return $next($request);
        }

        $context = app(CurrentDealershipContext::class);

        if ($context->get() === null) {
            return redirect('/admin/select-dealership');
        }

        return $next($request);
    }
}
