<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'tax_audit_id',
        'document_type', // Request, Response, Report, Evidence, Decision
        'title',
        'description',
        'file_path',
        'file_name',
        'submitted_by',
        'submitted_to',
        'submission_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(TaxAudit::class, 'tax_audit_id');
    }
}
