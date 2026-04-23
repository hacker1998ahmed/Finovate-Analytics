<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientPortal extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_id',
        'portal_type', // ETA, Customs, Insurance, EInvoice, Email, Other
        'portal_name',
        'username',
        'password_encrypted',
        'url',
        'is_active',
        'last_login',
        'notes',
    ];

    protected $hidden = ['password_encrypted'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(PortalAccessLog::class);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password_encrypted'] = encrypt($value);
    }

    public function getPasswordAttribute()
    {
        return decrypt($this->attributes['password_encrypted']);
    }
}
