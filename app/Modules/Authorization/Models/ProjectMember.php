<?php

namespace App\Modules\Authorization\Models;

use HieuDev92264\LaravelModules\Base\BaseModel;

class ProjectMember extends BaseModel
{
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
        return array_merge(parent::casts(), [
             'joined_at' => 'datetime',
             'left_at' => 'datetime',
        ]);
    }
}
