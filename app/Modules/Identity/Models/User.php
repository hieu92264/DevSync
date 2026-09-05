<?php

namespace App\Modules\Identity\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Modules\Authorization\Models\Role;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Organization;
use App\Modules\Organization\Models\OrganizationMember;
use App\Modules\Organization\Models\UserEquipment;
use Database\Factories\UserFactory;
use HieuDev92264\LaravelModules\traits\HasBaseMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasBaseMetadata;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_name',
        'email',
        'password',
        'last_login_at',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge($this->baseMetadataCasts(), [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ]);
    }

    //relationship
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withTimestamps()
            ->withPivot([
                'is_active',
                'user_name_created',
                'user_name_updated',
            ]);
    }

    public function getAllPermissions(): Collection
    {
        return $this->roles()->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->values();
    }

    public function hasPermission(string $permissionCode): bool
    {
        return $this->getAllPermissions()->contains('code', $permissionCode);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_members', 'user_id', 'organization_id')
            ->using(OrganizationMember::class)
            ->withPivot([
                'is_active',
                'user_name_created',
                'user_name_updated',
                'joined_at',
                'left_at',
            ])
            ->withTimestamps();
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_id', 'id');
    }

    public function equipmentAssignments(): HasMany
    {
        return $this->hasMany(UserEquipment::class, 'user_id', 'id');
    }
}
