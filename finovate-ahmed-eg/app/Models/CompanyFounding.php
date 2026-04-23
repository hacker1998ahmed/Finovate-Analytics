<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyFounding extends Model
{
    protected $fillable = [
        'company_id',
        'company_type',
        'registration_number',
        'commercial_register_number',
        'registration_date',
        'governorate',
        'address',
        'capital',
        'paid_capital',
        'activities',
        'status',
        'incorporation_date',
        'articles_of_association',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'incorporation_date' => 'date',
        'capital' => 'decimal:2',
        'paid_capital' => 'decimal:2',
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
