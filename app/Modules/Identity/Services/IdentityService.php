<?php

namespace App\Modules\Identity\Services;

use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Identity\Interfaces\IdentityServiceInterface;
use App\Modules\Identity\Models\User;

class IdentityService implements IdentityServiceInterface
{
    public function getOrganizationByUser(int $userId): array
    {
        $user = User::with(['organizations' => fn ($query) => $query->active()
            ->wherePivot('is_active', true)->whereNull('organization_members.left_at')])->findOrFail($userId);

        return $user->organizations->map(fn ($organization) => $organization->only(['id', 'code', 'name']))->values()->all();
    }

    public function getProjectsByUser(int $userId, int $organizationId): array
    {
        return ProjectMember::query()->active()->whereNull('left_at')->where('user_id', $userId)
            ->whereHas('project', fn ($query) => $query->active()->where('organization_id', $organizationId))
            ->with('project:id,organization_id,code,name,is_active')
            ->get()->map(fn (ProjectMember $member) => [
                'id' => $member->project->id,
                'code' => $member->project->code,
                'name' => $member->project->name,
                'team_type' => $member->team_type,
            ])->values()->all();
    }
}
