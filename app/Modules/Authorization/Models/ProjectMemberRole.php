<?php

namespace App\Modules\Authorization\Models;

use App\Modules\Authorization\Database\Factories\ProjectMemberRoleFactory;
use HieuDev92264\LaravelModules\traits\HasBaseMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectMemberRole extends Pivot
{
    use HasBaseMetadata, HasFactory;

    protected $table = 'project_member_roles';

    protected $fillable = [
        'project_member_id',
        'role_id',
        'assigned_at',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime'];
    }

    protected static function newFactory(): ProjectMemberRoleFactory
    {
        return ProjectMemberRoleFactory::new();
    }
}
