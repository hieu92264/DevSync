<?php

namespace App\Modules\Organization\Models;

use HieuDev92264\LaravelModules\Base\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'serial_number',
        'status',
        'specification',
        'purchase_at',
        'organization_id'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
             'purchase_at' => 'datetime',
        ]);
    }

    //relationship
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(UserEquipment::class, 'equipment_id', 'id');
    }
}
