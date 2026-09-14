<?php

namespace App\Modules\Authorization\Models;

use App\Modules\Authorization\Database\Factories\RoleFactory;
use HieuDev92264\LaravelModules\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseModel
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
        return array_merge(parent::casts(), [
            // 'date_column' => 'datetime',
        ]);
    }

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    // relationships
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
            ->withTimestamps()
            ->withPivot([
                'is_active',
                'user_name_created',
                'user_name_updated',
            ]);
    }
}
