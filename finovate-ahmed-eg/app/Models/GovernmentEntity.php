<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GovernmentEntity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'entity_type', // TaxAuthority, Customs, Insurance, Ministry, Other
        'code',
        'address',
        'phone',
        'email',
        'website',
        'working_hours',
        'contact_person',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function taxAudits(): HasMany
    {
        return $this->hasMany(TaxAudit::class);
    }

    public function complianceTasks(): HasMany
    {
        return $this->hasMany(ComplianceTask::class);
    }
}
