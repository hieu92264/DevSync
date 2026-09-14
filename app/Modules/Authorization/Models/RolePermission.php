<?php

namespace App\Modules\Authorization\Models;

use App\Modules\Authorization\Database\Factories\RolePermissionFactory;
use HieuDev92264\LaravelModules\traits\HasBaseMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermission extends Pivot
{
    use HasBaseMetadata, HasFactory;

    protected $table = 'role_permissions';

    protected $fillable = ['role_id', 'permission_id'];

    protected static function newFactory(): RolePermissionFactory
    {
        return RolePermissionFactory::new();
    }
}
