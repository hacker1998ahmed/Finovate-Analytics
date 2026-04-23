<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    // Invoice Status Constants
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_VALID = 'VALID';
    const STATUS_INVALID = 'INVALID';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_CANCELLED = 'CANCELLED';

    // Invoice Type Constants
    const TYPE_B2B = 'B2B';
    const TYPE_B2C = 'B2C';

    protected $fillable = [
        'company_id',
        'uuid',
        'invoice_number',
        'invoice_type',
        'issue_date',
        'delivery_date',
        'seller_name',
        'seller_tax_id',
        'seller_address',
        'buyer_name',
        'buyer_tax_id',
        'buyer_vat_number',
        'buyer_address',
        'buyer_email',
        'buyer_phone',
        'subtotal',
        'discount_total',
        'tax_total',
        'net_total',
        'total_amount',
        'eta_submission_id',
        'status',
        'eta_response',
        'rejection_reason',
        'document_hash',
        'signature',
        'is_synced',
        'synced_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'net_total' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'eta_response' => 'array',
        'is_synced' => 'boolean',
        'synced_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->uuid)) {
                $invoice->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the company that owns the invoice.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the items for the invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Calculate invoice totals.
     */
    public function calculateTotals(): void
    {
        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        foreach ($this->items as $item) {
            $subtotal += $item->subtotal;
            $discountTotal += $item->discount_amount;
            $taxTotal += $item->tax_amount;
        }

        $this->subtotal = $subtotal + $discountTotal; // Subtotal before discount
        $this->discount_total = $discountTotal;
        $this->tax_total = $taxTotal;
        $this->net_total = $subtotal;
        $this->total_amount = $subtotal + $taxTotal;
    }

    /**
     * Check if invoice is submitted to ETA.
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUBMITTED,
            self::STATUS_VALID,
            self::STATUS_INVALID,
            self::STATUS_REJECTED,
        ]);
    }

    /**
     * Check if invoice is valid.
     */
    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }

    /**
     * Check if invoice is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get rejection reasons.
     */
    public function getRejectionReasonsAttribute(): array
    {
        if (!$this->rejection_reason) {
            return [];
        }

        try {
            return json_decode($this->rejection_reason, true) ?? [$this->rejection_reason];
        } catch (\Exception $e) {
            return [$this->rejection_reason];
        }
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by buyer.
     */
    public function scopeBuyer($query, string $buyerName)
    {
        return $query->where('buyer_name', 'like', "%{$buyerName}%");
    }

    /**
     * Scope for filtering by invoice number.
     */
    public function scopeInvoiceNumber($query, string $invoiceNumber)
    {
        return $query->where('invoice_number', 'like', "%{$invoiceNumber}%");
    }
}
