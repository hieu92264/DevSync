<?php

namespace App\Modules\Authorization\Services;

use App\Modules\Identity\Models\User;

interface AuthorizationServiceInterface
{
    public function can(User $user, string $permission, RequestContext $context): bool;

    /** @return array<int, string> */
    public function permissionCodes(User $user, RequestContext $context): array;

    /** @return array<int, string> */
    public function projectRoleCodes(RequestContext $context): array;
}
