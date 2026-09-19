<?php

namespace App\Modules\Authorization\Services;

use App\Modules\Authorization\Models\Permission;
use App\Modules\Authorization\Models\Role;
use App\Modules\Identity\Models\User;

class AuthorizationService implements AuthorizationServiceInterface
{
    public function can(User $user, string $permission, RequestContext $context): bool
    {
        if (! config('authorization.enforced')) {
            return true;
        }

        return in_array($permission, $this->permissionCodes($user, $context), true);
    }

    public function permissionCodes(User $user, RequestContext $context): array
    {
        if (! $user->is_active || ! $context->organizationMember?->is_active) {
            return [];
        }

        $membership = $context->projectMember ?? $context->organizationMember;
        $scope = $context->projectMember ? Role::SCOPE_PROJECT : Role::SCOPE_ORGANIZATION;
        if ($context->projectMember && ! $context->projectMember->is_active) {
            return [];
        }

        return Permission::query()
            ->select('permissions.code')
            ->active()
            ->join('role_permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('role_permissions.is_active', true)
            ->join('roles', 'roles.id', '=', 'role_permissions.role_id')
            ->where('roles.is_active', true)
            ->where('roles.scope', $scope)
            ->where('roles.organization_id', $context->organization->id)
            ->when($context->projectMember, fn ($query) => $query
                ->join('project_member_roles', 'project_member_roles.role_id', '=', 'roles.id')
                ->where('project_member_roles.project_member_id', $membership->id)
                ->where('project_member_roles.is_active', true), fn ($query) => $query
                ->join('organization_member_roles', 'organization_member_roles.role_id', '=', 'roles.id')
                ->where('organization_member_roles.organization_member_id', $membership->id)
                ->where('organization_member_roles.is_active', true))
            ->distinct()
            ->orderBy('permissions.code')
            ->pluck('permissions.code')
            ->all();
    }

    public function projectRoleCodes(RequestContext $context): array
    {
        if (! $context->projectMember?->is_active) {
            return [];
        }

        return Role::query()->active()
            ->where('organization_id', $context->organization->id)
            ->where('scope', Role::SCOPE_PROJECT)
            ->whereHas('projectMembers', fn ($query) => $query
                ->where('project_members.id', $context->projectMember->id)
                ->where('project_member_roles.is_active', true))
            ->orderBy('code')
            ->pluck('code')->all();
    }
}
