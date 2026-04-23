<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_founding_id',
        'partner_type', // Individual, Corporate
        'name',
        'national_id',
        'tax_number',
        'email',
        'phone',
        'address',
        'ownership_percentage',
        'share_count',
        'share_value',
        'is_manager',
        'manager_authorities',
        'signature_authority',
        'status',
        'notes',
    ];

    protected $casts = [
        'ownership_percentage' => 'decimal:2',
        'share_value' => 'decimal:2',
        'is_manager' => 'boolean',
        'signature_authority' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function founding(): BelongsTo
    {
        return $this->belongsTo(CompanyFounding::class, 'company_founding_id');
    }
}
