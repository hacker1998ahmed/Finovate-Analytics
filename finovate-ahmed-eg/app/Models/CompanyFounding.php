<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyFounding extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'founding_type', // LLC, JSC, Establishment, Partnership
        'commercial_name',
        'legal_name',
        'registration_number',
        'tax_number',
        'registration_date',
        'capital_amount',
        'paid_capital',
        'activity_code',
        'activity_description',
        'address',
        'city',
        'governorate',
        'manager_name',
        'manager_national_id',
        'manager_phone',
        'status', // Draft, Submitted, Approved, Rejected
        'approval_date',
        'notes',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'approval_date' => 'date',
        'capital_amount' => 'decimal:2',
        'paid_capital' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class);
    }
}
