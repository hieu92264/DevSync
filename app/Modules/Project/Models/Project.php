<?php

namespace App\Modules\Project\Models;

use App\Modules\Authorization\Models\ProjectMember;
use App\Modules\Identity\Models\User;
use App\Modules\Organization\Models\Organization;
use App\Modules\Project\Database\Factories\ProjectFactory;
use HieuDev92264\LaravelModules\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends BaseModel
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

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }

    // relationships
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members', 'project_id', 'user_id')
            ->using(ProjectMember::class)
            ->withPivot([
                'team_type',
                'joined_at',
                'left_at',
                'is_active',
                'user_name_created',
                'user_name_updated',
            ])
            ->withTimestamps();
    }

    public function projectMembers(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }
}
