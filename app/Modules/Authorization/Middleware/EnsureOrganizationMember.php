<?php

namespace App\Modules\Authorization\Middleware;

use App\Modules\Authorization\Services\RequestContext;
use App\Modules\Organization\Models\OrganizationMember;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureOrganizationMember
{
    public function handle(Request $request, Closure $next): mixed
    {
        $context = app(RequestContext::class);
        $member = OrganizationMember::query()->active()
            ->whereNull('left_at')
            ->where('organization_id', $context->organization?->id)
            ->where('user_id', $request->user()->id)->first();
        if (! $member) {
            throw new AccessDeniedHttpException('You are not an active organization member.');
        }
        $context->organizationMember = $member;

        return $next($request);
    }
}
