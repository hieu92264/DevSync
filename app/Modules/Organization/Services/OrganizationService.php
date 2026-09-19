<?php

namespace App\Modules\Organization\Services;

use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Organization\Interfaces\OrganizationServiceInterface;
use App\Modules\Organization\Models\Organization;
use Illuminate\Auth\Access\AuthorizationException;

class OrganizationService implements OrganizationServiceInterface
{
    public function getDashboardByUser(int $userId, Organization $organization): array
    {
        $hasActiveMembership = $organization->members()
            ->whereKey($userId)
            ->wherePivot('is_active', true)
            ->wherePivotNull('left_at')
            ->exists();

        if (config('authorization.enforced') && ! $hasActiveMembership) {
            throw new AuthorizationException('You are not an active member of this organization.');
        }

        $memberships = ProjectMember::query()
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->whereNull('left_at')
            ->whereHas('project', fn ($query) => $query
                ->where('organization_id', $organization->id)
                ->where('is_active', true))
            ->with([
                'project:id,code,name,status,organization_id,lead_id',
                'roles' => fn ($query) => $query
                    ->where('roles.is_active', true)
                    ->where('project_member_roles.is_active', true),
                'roles.permissions' => fn ($query) => $query
                    ->where('permissions.is_active', true)
                    ->where('role_permissions.is_active', true),
            ])
            ->get();

        return [
            'organization' => $organization->only(['id', 'code', 'name']),
            'projects' => $memberships->map(fn (ProjectMember $membership) => [
                'membership_id' => $membership->id,
                'project' => $membership->project?->only(['id', 'code', 'name', 'status']),
                'roles' => $membership->roles->map(fn ($role) => [
                    'id' => $role->id,
                    'code' => $role->code,
                    'name' => $role->name,
                    'priority' => $role->priority,
                    'permissions' => $role->permissions->map(fn ($permission) => [
                        'code' => $permission->code,
                        'name' => $permission->name,
                        'resource' => $permission->resource,
                        'action' => $permission->action,
                    ])->values()->all(),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
