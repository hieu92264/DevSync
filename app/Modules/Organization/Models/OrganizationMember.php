<?php

namespace App\Modules\Organization\Models;

use App\Modules\Organization\Database\Factories\OrganizationMemberFactory;
use HieuDev92264\LaravelModules\traits\HasBaseMetadata;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationMember extends Pivot
{
    use HasBaseMetadata, HasFactory;

    protected $table = 'organization_members';

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
        return array_merge($this->baseMetadataCasts(), [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
        ]);
    }

    protected static function newFactory(): OrganizationMemberFactory
    {
        return OrganizationMemberFactory::new();
    }
}
