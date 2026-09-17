<?php

namespace App\Modules\Authorization\Middleware;

use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Authorization\Services\RequestContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureProjectMember
{
    public function handle(Request $request, Closure $next): mixed
    {
        $context = app(RequestContext::class);
        $member = ProjectMember::query()->active()->whereNull('left_at')
            ->where('project_id', $context->project?->id)->where('user_id', $request->user()->id)->first();
        if (! $member) {
            throw new AccessDeniedHttpException('You are not an active project member.');
        }
        $context->projectMember = $member;

        return $next($request);
    }
}
