<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Interfaces\IdentityServiceInterface;
use App\Modules\Identity\Models\User;

class IdentityService implements IdentityServiceInterface
{
    public function getOrganizationByUser(int $userId): array
    {
        $user = User::with(['organizations' => function ($query) {
            $query->wherePivot('is_active', true)
            ->whereNull('organization_members.left_at');
        }])->findOrFail($userId);

        return $user->organizations->toArray();
    }
}
