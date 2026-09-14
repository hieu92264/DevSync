<?php

namespace App\Modules\Organization\Interfaces;

use App\Modules\Organization\Models\Organization;

interface OrganizationServiceInterface
{
    public function getDashboardByUser(int $userId, Organization $organization): array;
}
