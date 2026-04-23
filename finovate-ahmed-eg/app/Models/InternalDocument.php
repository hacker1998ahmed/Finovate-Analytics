<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class InternalDocument extends Model
{
    protected $fillable = [
        'company_id',
        'document_category',
        'document_type',
        'title',
        'reference_number',
        'document_date',
        'expiry_date',
        'created_by',
        'department_id',
        'file_path',
        'file_name',
        'file_size',
        'description',
        'metadata',
        'is_confidential',
        'is_signed',
    ];

    protected $casts = [
        'document_date' => 'date',
        'expiry_date' => 'date',
        'file_size' => 'integer',
        'metadata' => 'array',
        'is_confidential' => 'boolean',
        'is_signed' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(DocumentTag::class, 'taggable');
    }
}
