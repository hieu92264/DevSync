<?php

namespace App\Modules\Authorization\Middleware;

use App\Modules\Authorization\Services\RequestContext;
use App\Modules\Project\Models\Project;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveProjectContext
{
    public function handle(Request $request, Closure $next): mixed
    {
        $code = $request->header('X-Project-Code');
        if (! is_string($code) || trim($code) === '') {
            throw new BadRequestHttpException('X-Project-Code header is required.');
        }
        $project = Project::query()->active()->where('organization_id', app(RequestContext::class)->organization?->id)
            ->where('code', $code)->first();
        if (! $project) {
            throw new NotFoundHttpException('Project not found.');
        }
        app(RequestContext::class)->project = $project;

        return $next($request);
    }
}
