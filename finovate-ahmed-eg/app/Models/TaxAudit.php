<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'audit_type', // Tax, Table, Random, Special
        'audit_number',
        'tax_year',
        'start_date',
        'end_date',
        'status', // Planned, InProgress, Completed, Appealed, Closed
        'auditor_name',
        'government_entity_id',
        'total_assessed_tax',
        'total_penalties',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'objection_deadline',
        'objection_filed',
        'objection_date',
        'appeal_status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'objection_deadline' => 'date',
        'objection_filed' => 'boolean',
        'objection_date' => 'date',
        'total_assessed_tax' => 'decimal:2',
        'total_penalties' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function governmentEntity(): BelongsTo
    {
        return $this->belongsTo(GovernmentEntity::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AuditDocument::class);
    }

    public function complianceTasks(): HasMany
    {
        return $this->hasMany(ComplianceTask::class);
    }
}
