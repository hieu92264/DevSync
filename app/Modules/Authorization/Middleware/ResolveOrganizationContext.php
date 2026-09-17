<?php

namespace App\Modules\Authorization\Middleware;

use App\Modules\Authorization\Services\RequestContext;
use App\Modules\Organization\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveOrganizationContext
{
    public function handle(Request $request, Closure $next): mixed
    {
        $code = $request->header('X-Organization-Code');
        if (! is_string($code) || trim($code) === '') {
            throw new BadRequestHttpException('X-Organization-Code header is required.');
        }

        $organization = Organization::query()->active()->where('code', $code)->first();
        if (! $organization) {
            throw new NotFoundHttpException('Organization not found.');
        }

        $context = app(RequestContext::class);
        // A scoped binding is reset by Laravel workers; clearing descendants here also
        // protects sequential requests in long-lived/testing application instances.
        $context->organization = $organization;
        $context->organizationMember = null;
        $context->project = null;
        $context->projectMember = null;

        return $next($request);
    }
}
