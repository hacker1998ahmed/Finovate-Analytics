<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    const TAX_TYPE_VAT = 'VAT';
    const TAX_TYPE_TABLE_TAX = 'TABLE_TAX';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'unit_price',
        'tax_type',
        'tax_rate',
        'unit_type',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns the item.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the invoice items.
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Check if item is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope for active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for filtering by company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Prepare for ETA API format.
     */
    public function toEtaFormat(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description ?? '',
            'price' => (float) $this->unit_price,
            'taxType' => $this->tax_type === self::TAX_TYPE_TABLE_TAX ? 'T' : 'V',
            'taxRate' => (float) $this->tax_rate,
            'unitType' => $this->unit_type,
        ];
    }
}
