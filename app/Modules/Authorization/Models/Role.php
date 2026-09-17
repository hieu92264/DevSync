<?php

namespace App\Modules\Authorization\Models;

use App\Modules\Authorization\Database\Factories\RoleFactory;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationMember;
use App\Modules\Organization\Models\OrganizationMemberRole;
use HieuDev92264\LaravelModules\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseModel
{
    use HasFactory;

    public const SCOPE_ORGANIZATION = 'ORGANIZATION';

    public const SCOPE_PROJECT = 'PROJECT';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organization_id',
        'scope',
        'code',
        'name',
        'priority',
        'remark',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), []);
    }

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    // relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
            ->using(RolePermission::class)
            ->withPivot(['is_active', 'user_name_created', 'user_name_updated'])
            ->withTimestamps();
    }

    public function organizationMembers(): BelongsToMany
    {
        return $this->belongsToMany(OrganizationMember::class, 'organization_member_roles', 'role_id', 'organization_member_id', 'id', 'id')
            ->using(OrganizationMemberRole::class)
            ->withPivot(['assigned_at', 'assigned_by', 'is_active', 'user_name_created', 'user_name_updated'])
            ->withTimestamps();
    }

    public function projectMembers(): BelongsToMany
    {
        return $this->belongsToMany(ProjectMember::class, 'project_member_roles', 'role_id', 'project_member_id', 'id', 'id')
            ->using(ProjectMemberRole::class)
            ->withPivot(['assigned_at', 'assigned_by', 'is_active', 'user_name_created', 'user_name_updated'])
            ->withTimestamps();
    }
}
