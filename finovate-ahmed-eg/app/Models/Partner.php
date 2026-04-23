<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partner extends Model
{
    protected $fillable = [
        'company_founding_id',
        'partner_type',
        'name',
        'national_id',
        'passport_number',
        'tax_id',
        'shares_percentage',
        'shares_value',
        'position',
        'is_manager',
        'address',
        'phone',
        'email',
    ];

    protected $casts = [
        'is_manager' => 'boolean',
        'shares_percentage' => 'decimal:2',
        'shares_value' => 'decimal:2',
    ];

    public function companyFounding(): BelongsTo
    {
        return $this->belongsTo(CompanyFounding::class);
    }
}
