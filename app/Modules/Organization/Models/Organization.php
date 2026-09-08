<?php

namespace App\Modules\Organization\Models;
use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Identity\Models\User;
use App\Modules\Project\Models\Project;
use HieuDev92264\LaravelModules\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'organization_id', 'id');
    }

    public function projectMembers(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProjectMember::class,
            Project::class,
            'organization_id',
            'project_id',
            'id',
            'id'
        );
    }
}
