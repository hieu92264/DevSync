<?php

namespace App\Modules\Authorization\Models;

use App\Modules\Authorization\Database\Factories\ProjectMemberFactory;
use App\Modules\Project\Models\Project;
use HieuDev92264\LaravelModules\traits\HasBaseMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectMember extends Pivot
{
    use HasBaseMetadata, HasFactory;

    public $incrementing = true;

    protected $keyType = 'int';

    protected $table = 'project_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'user_id',
        'team_type',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'project_member_roles', 'project_member_id', 'role_id')
            ->withTimestamps()
            ->withPivot(['is_active', 'user_name_created', 'user_name_updated']);
    }

    protected static function newFactory(): ProjectMemberFactory
    {
        return ProjectMemberFactory::new();
    }
}
