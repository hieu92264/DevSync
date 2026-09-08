<?php

namespace App\Modules\Project\Models;


use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Identity\Models\User;
use App\Modules\Organization\Models\Organization;
use HieuDev92264\LaravelModules\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'repository_url',
        'organization_id',
        'lead_id',
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

    // relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function ProjectMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'user_id')
            ->using(ProjectMember::class)
            ->withPivot([
                'team_type',
                'joined_at',
                'left_at',
                'user_name_created',
                'user_name_updated',
                'is_active',
            ])
            ->withTimestamps();
    }
}
