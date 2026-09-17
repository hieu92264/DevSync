<?php

namespace App\Modules\Authorization\Services;

use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationMember;
use App\Modules\Project\Models\Project;

class RequestContext
{
    public ?Organization $organization = null;

    public ?OrganizationMember $organizationMember = null;

    public ?Project $project = null;

    public ?ProjectMember $projectMember = null;
}
