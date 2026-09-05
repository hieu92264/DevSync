<?php

namespace App\Modules\Organization\Models;

use HieuDev92264\LaravelModules\Base\BaseModel;

class OrganizationMember extends BaseModel
{
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
        return array_merge(parent::casts(), [
             'joined_at' => 'datetime',
             'left_at' => 'datetime',
        ]);
    }
}
