<?php

namespace App\Modules\Organization\Controllers;

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

class OrganizationMemberController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $organizationId = app(RequestContext::class)->organization->id;
        $members = OrganizationMember::query()->where('organization_id', $organizationId)->active()->whereNull('left_at')
            ->with('user:id,user_name,email')->get()->map(fn ($member) => [
                'id' => $member->id, 'user' => $member->user, 'joined_at' => $member->joined_at,
            ])->values();

        return $this->success($members, 'Organization members retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $user = User::query()->active()->findOrFail($data['user_id']);
        $organizationId = app(RequestContext::class)->organization->id;
        $member = DB::transaction(function () use ($organizationId, $user) {
            $member = OrganizationMember::firstOrNew(['organization_id' => $organizationId, 'user_id' => $user->id]);
            $member->fill(['joined_at' => now(), 'left_at' => null, 'is_active' => true])->save();

            return $member;
        });

        return $this->success($member->only(['id', 'organization_id', 'user_id']), 'Organization member added successfully', 201);
    }

    public function destroy(int $member): JsonResponse
    {
        $membership = $this->member($member);
        $membership->update(['is_active' => false, 'left_at' => now()]);

        return $this->success(null, 'Organization member removed successfully');
    }

    public function roles(): JsonResponse
    {
        $roles = Role::query()->active()->where('organization_id', app(RequestContext::class)->organization->id)
            ->where('scope', Role::SCOPE_ORGANIZATION)->orderBy('code')->get(['id', 'code', 'name']);

        return $this->success($roles, 'Organization roles retrieved successfully');
    }

    public function updateRoles(Request $request, int $member): JsonResponse
    {
        $data = $request->validate(['role_ids' => ['present', 'array'], 'role_ids.*' => ['integer', 'distinct']]);
        $membership = $this->member($member);
        $roles = Role::query()->active()->where('organization_id', app(RequestContext::class)->organization->id)
            ->where('scope', Role::SCOPE_ORGANIZATION)->whereIn('id', $data['role_ids'])->get();
        if ($roles->count() !== count($data['role_ids'])) {
            throw new NotFoundHttpException('Role not found.');
        }
        DB::transaction(fn () => $membership->roles()->syncWithPivotValues($roles->modelKeys(), [
            'assigned_at' => now(), 'assigned_by' => request()->user()->id, 'is_active' => true,
        ]));

        return $this->success(null, 'Organization member roles updated successfully');
    }

    private function member(int $id): OrganizationMember
    {
        return OrganizationMember::query()->where('organization_id', app(RequestContext::class)->organization->id)->findOrFail($id);
    }
}
