<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class InternalDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'documentable_id',
        'documentable_type',
        'document_type', // Contract, Memo, Decision, Report, Letter, Other
        'title',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'is_confidential',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'is_confidential' => 'boolean',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(DocumentTag::class, 'taggable', 'document_taggable');
    }
}
