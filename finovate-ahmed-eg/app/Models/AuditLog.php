<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    // Action Constants
    const ACTION_CREATED = 'CREATED';
    const ACTION_UPDATED = 'UPDATED';
    const ACTION_DELETED = 'DELETED';
    const ACTION_SUBMITTED = 'SUBMITTED';
    const ACTION_CANCELLED = 'CANCELLED';
    const ACTION_SYNCED = 'SYNCED';
    const ACTION_LOGIN = 'LOGIN';
    const ACTION_LOGOUT = 'LOGOUT';

    protected $fillable = [
        'company_id',
        'user_type',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the company for the audit log.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the model that was audited.
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Scope for filtering by company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope for filtering by action.
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for filtering by model.
     */
    public function scopeForModel($query, string $modelType, $modelId = null)
    {
        $query = $query->where('model_type', $modelType);
        
        if ($modelId !== null) {
            $query = $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Log a create action.
     */
    public static function logCreate(Model $model, array $changes = [], ?int $companyId = null): self
    {
        return static::create([
            'company_id' => $companyId ?? ($model->company_id ?? null),
            'user_type' => auth()->check() ? get_class(auth()->user()) : 'System',
            'user_id' => auth()->id(),
            'action' => self::ACTION_CREATED,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'new_values' => $changes ?: $model->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log an update action.
     */
    public static function logUpdate(Model $model, array $oldValues, array $newValues, ?int $companyId = null): self
    {
        return static::create([
            'company_id' => $companyId ?? ($model->company_id ?? null),
            'user_type' => auth()->check() ? get_class(auth()->user()) : 'System',
            'user_id' => auth()->id(),
            'action' => self::ACTION_UPDATED,
            'model_type' => get_class($model),
            'model_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log a custom action.
     */
    public static function log(string $action, ?Model $model = null, array $data = [], ?int $companyId = null): self
    {
        return static::create([
            'company_id' => $companyId,
            'user_type' => auth()->check() ? get_class(auth()->user()) : 'System',
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
            'new_values' => $data,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
