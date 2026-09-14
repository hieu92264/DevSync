<?php

namespace App\Modules\Organization\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Organization\Database\Factories\DepartmentFactory;
use HieuDev92264\LaravelModules\Base\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends BaseModel
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
        'organization_id',
        'manager_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'organization_id' => 'integer',
            'manager_id' => 'integer',
        ]);
    }

    protected static function newFactory(): DepartmentFactory
    {
        return DepartmentFactory::new();
    }

    // relationship
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }
}
