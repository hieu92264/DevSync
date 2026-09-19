<?php

namespace App\Modules\Project\Controllers;

use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Authorization\Models\Role;
use App\Modules\Authorization\Services\RequestContext;
use App\Modules\Identity\Models\User;
use App\Modules\Organization\Models\OrganizationMember;
use HieuDev92264\LaravelModules\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProjectMemberController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $members = ProjectMember::query()->where('project_id', app(RequestContext::class)->project->id)->active()->whereNull('left_at')
            ->with('user:id,user_name,email')->get()->map(fn ($member) => [
                'id' => $member->id, 'user' => $member->user, 'team_type' => $member->team_type, 'joined_at' => $member->joined_at,
            ])->values();

        return $this->success($members, 'Project members retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'team_type' => ['nullable', 'string', 'max:50'],
        ]);
        $context = app(RequestContext::class);
        User::query()->active()->findOrFail($data['user_id']);
        $organizationMember = OrganizationMember::query()->active()->whereNull('left_at')
            ->where('organization_id', $context->organization->id)->where('user_id', $data['user_id'])->exists();
        if (! $organizationMember) {
            throw new NotFoundHttpException('User not found.');
        }
        $member = DB::transaction(function () use ($data, $context) {
            $member = ProjectMember::firstOrNew(['project_id' => $context->project->id, 'user_id' => $data['user_id']]);
            $member->fill(['team_type' => $data['team_type'] ?? null, 'joined_at' => now(), 'left_at' => null, 'is_active' => true])->save();

            return $member;
        });

        return $this->success($member->only(['id', 'project_id', 'user_id', 'team_type']), 'Project member added successfully', 201);
    }

    public function destroy(int $member): JsonResponse
    {
        $membership = $this->member($member);
        $membership->update(['is_active' => false, 'left_at' => now()]);

        return $this->success(null, 'Project member removed successfully');
    }

    public function roles(): JsonResponse
    {
        $roles = Role::query()->active()->where('organization_id', app(RequestContext::class)->organization->id)
            ->where('scope', Role::SCOPE_PROJECT)->orderBy('code')->get(['id', 'code', 'name']);

        return $this->success($roles, 'Project roles retrieved successfully');
    }

    public function updateRoles(Request $request, int $member): JsonResponse
    {
        $data = $request->validate(['role_ids' => ['present', 'array'], 'role_ids.*' => ['integer', 'distinct']]);
        $membership = $this->member($member);
        $roles = Role::query()->active()->where('organization_id', app(RequestContext::class)->organization->id)
            ->where('scope', Role::SCOPE_PROJECT)->whereIn('id', $data['role_ids'])->get();
        if ($roles->count() !== count($data['role_ids'])) {
            throw new NotFoundHttpException('Role not found.');
        }
        DB::transaction(fn () => $membership->roles()->syncWithPivotValues($roles->modelKeys(), [
            'assigned_at' => now(), 'assigned_by' => request()->user()->id, 'is_active' => true,
        ]));

        return $this->success(null, 'Project member roles updated successfully');
    }

    private function member(int $id): ProjectMember
    {
        return ProjectMember::query()->where('project_id', app(RequestContext::class)->project->id)->findOrFail($id);
    }
}
