<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'tax_audit_id',
        'government_entity_id',
        'task_type', // Filing, Payment, Response, Meeting, Submission
        'title',
        'description',
        'due_date',
        'priority', // Low, Medium, High, Urgent
        'status', // Pending, InProgress, Completed, Overdue, Cancelled
        'assigned_to',
        'completed_at',
        'completed_by',
        'reminder_sent',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'reminder_sent' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function taxAudit(): BelongsTo
    {
        return $this->belongsTo(TaxAudit::class);
    }

    public function governmentEntity(): BelongsTo
    {
        return $this->belongsTo(GovernmentEntity::class);
    }
}
