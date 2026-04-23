<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxAudit extends Model
{
    protected $fillable = [
        'company_id',
        'audit_type',
        'tax_year',
        'notification_date',
        'start_date',
        'end_date',
        'auditor_name',
        'tax_office',
        'assessed_tax',
        'penalties',
        'additional_charges',
        'status',
        'findings',
        'objections',
        'objection_deadline',
        'appeal_date',
        'final_decision',
    ];

    protected $casts = [
        'notification_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'objection_deadline' => 'date',
        'appeal_date' => 'date',
        'assessed_tax' => 'decimal:2',
        'penalties' => 'decimal:2',
        'additional_charges' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AuditDocument::class);
    }
}
