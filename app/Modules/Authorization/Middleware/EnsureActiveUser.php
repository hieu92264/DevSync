<?php

namespace App\Modules\Authorization\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->user()?->is_active) {
            throw new AccessDeniedHttpException('This user is inactive.');
        }

        return $next($request);
    }
}
