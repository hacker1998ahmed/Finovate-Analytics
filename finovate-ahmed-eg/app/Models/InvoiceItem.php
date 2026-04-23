<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_id',
        'item_code',
        'item_name',
        'item_description',
        'quantity',
        'unit_type',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_type',
        'tax_rate',
        'tax_amount',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the related item from master items.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Calculate item totals.
     */
    public function calculateTotals(): void
    {
        // Calculate line subtotal: (quantity * unit_price) - discount
        $lineAmount = $this->quantity * $this->unit_price;
        
        // Apply discount if not already set
        if ($this->discount_amount == 0 && $this->discount_percent > 0) {
            $this->discount_amount = $lineAmount * ($this->discount_percent / 100);
        }
        
        $this->subtotal = $lineAmount - $this->discount_amount;
        
        // Calculate tax
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        
        // Calculate total
        $this->total = $this->subtotal + $this->tax_amount;
    }

    /**
     * Prepare for ETA API format.
     */
    public function toEtaFormat(): array
    {
        return [
            'code' => $this->item_code,
            'description' => $this->item_name . ($this->item_description ? ' - ' . $this->item_description : ''),
            'itemType' => 'EGS', // Standard item type
            'unitType' => $this->unit_type, // EA, BOX, etc.
            'quantity' => (float) $this->quantity,
            'netPrice' => (float) $this->unit_price,
            'total' => (float) ($this->quantity * $this->unit_price),
            'discount' => [
                'discountReason' => $this->discount_percent > 0 ? 'Other' : null,
                'discountPercent' => (float) $this->discount_percent,
                'discountAmount' => (float) $this->discount_amount,
            ],
            'taxableItems' => [
                [
                    'taxType' => $this->tax_type === 'TABLE_TAX' ? 'T' : 'V', // V for VAT, T for Table Tax
                    'amount' => (float) $this->tax_amount,
                    'subTotal' => (float) $this->subtotal,
                    'rate' => (float) $this->tax_rate,
                ]
            ],
            'internalCode' => $this->item_code,
        ];
    }
}
