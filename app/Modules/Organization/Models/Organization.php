<?php

namespace App\Modules\Organization\Models;
use App\Modules\Identity\Models\User;
use HieuDev92264\LaravelModules\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
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

    //relationship
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'organization_id', 'id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'organization_id', 'id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_members', 'organization_id', 'user_id')
            ->using(OrganizationMember::class)
             ->withPivot([
                 'joined_at',
                 'left_at',
                 'user_name_created',
                 'user_name_updated',
                 'is_active',
             ])
            ->withTimestamps();
    }
}
