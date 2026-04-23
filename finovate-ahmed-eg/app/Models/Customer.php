<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'tax_number',
        'national_id',
        'email',
        'phone',
        'address',
        'city',
        'governorate',
        'postal_code',
        'country',
        'activity_type',
        'customer_type',
        'credit_limit',
        'balance',
        'status',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'buyer_id');
    }

    public function portals(): HasMany
    {
        return $this->hasMany(ClientPortal::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(InternalDocument::class, 'documentable');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
