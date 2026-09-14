<?php

namespace App\Modules\Organization\Models;

use App\Modules\Organization\Database\Factories\OrganizationMemberRoleFactory;
use HieuDev92264\LaravelModules\traits\HasBaseMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationMemberRole extends Pivot
{
    use HasBaseMetadata, HasFactory;

    protected $table = 'organization_member_roles';

    protected $fillable = [
        'organization_member_id',
        'role_id',
        'assigned_at',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime'];
    }

    protected static function newFactory(): OrganizationMemberRoleFactory
    {
        return OrganizationMemberRoleFactory::new();
    }
}
