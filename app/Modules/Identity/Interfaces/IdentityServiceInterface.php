<?php

namespace App\Modules\Identity\Interfaces;

interface IdentityServiceInterface
{
    public function getOrganizationByUser(int $userId): array;
}
