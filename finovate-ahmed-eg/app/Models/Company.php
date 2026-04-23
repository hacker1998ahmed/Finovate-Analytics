<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tax_registration_number',
        'eta_client_id',
        'eta_client_secret',
        'digital_certificate',
        'certificate_password',
        'api_base_url',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the invoices for the company.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the items for the company.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get the ETA token for the company.
     */
    public function etaToken(): HasMany
    {
        return $this->hasMany(EtaToken::class);
    }

    /**
     * Get the latest ETA token.
     */
    public function latestEtaToken()
    {
        return $this->etaToken()->latest()->first();
    }

    /**
     * Check if company is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get decrypted client secret.
     */
    public function getDecryptedClientSecretAttribute(): string
    {
        return decrypt($this->eta_client_secret);
    }

    /**
     * Get decrypted certificate password.
     */
    public function getDecryptedCertificatePasswordAttribute(): ?string
    {
        if (!$this->certificate_password) {
            return null;
        }
        return decrypt($this->certificate_password);
    }
}
