<?php

namespace App\Modules\Authorization\Models;

use App\Modules\Authorization\Database\Factories\PermissionFactory;
use HieuDev92264\LaravelModules\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'remark',
        'resource',
        'action',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            // 'date_column' => 'datetime',
        ]);
    }

    protected static function newFactory(): PermissionFactory
    {
        return PermissionFactory::new();
    }

    // relationship
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id')
            ->withTimestamps()
            ->withPivot(['is_active', 'user_name_created', 'user_name_updated']);
    }
}
