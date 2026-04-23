<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditDocument extends Model
{
    protected $fillable = [
        'tax_audit_id',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'submission_date',
        'notes',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'file_size' => 'integer',
    ];

    public function taxAudit(): BelongsTo
    {
        return $this->belongsTo(TaxAudit::class);
    }
}
