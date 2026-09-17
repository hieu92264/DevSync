<?php

namespace App\Modules\Authorization\Middleware;

use App\Modules\Authorization\Services\AuthorizationServiceInterface;
use App\Modules\Authorization\Services\RequestContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        if (! app(AuthorizationServiceInterface::class)->can($request->user(), $permission, app(RequestContext::class))) {
            throw new AccessDeniedHttpException('You do not have the required permission.');
        }

        return $next($request);
    }
}
