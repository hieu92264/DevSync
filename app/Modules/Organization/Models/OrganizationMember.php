<?php

namespace App\Modules\Organization\Models;

use App\Modules\Authorization\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Organization\Database\Factories\OrganizationMemberFactory;
use HieuDev92264\LaravelModules\traits\HasBaseMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationMember extends Pivot
{
    use HasBaseMetadata, HasFactory;

    public $incrementing = true;

    protected $primaryKey = 'id';

    protected $table = 'organization_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organization_id',
        'user_id',
        'joined_at',
        'left_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge($this->baseMetadataCasts(), [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ]);
    }

    protected static function newFactory(): OrganizationMemberFactory
    {
        return OrganizationMemberFactory::new();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'organization_member_roles', 'organization_member_id', 'role_id', 'id', 'id')
            ->using(OrganizationMemberRole::class)
            ->withPivot(['assigned_at', 'assigned_by', 'is_active', 'user_name_created', 'user_name_updated'])
            ->withTimestamps();
    }
}
