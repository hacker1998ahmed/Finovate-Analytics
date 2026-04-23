<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NationalIdRegistry extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'national_id',
        'name',
        'birth_date',
        'gender',
        'address',
        'governorate',
        'city',
        'id_issue_date',
        'id_expiry_date',
        'id_place',
        'phone',
        'email',
        'related_to_type',
        'related_to_id',
        'verified',
        'verification_date',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'id_issue_date' => 'date',
        'id_expiry_date' => 'date',
        'verified' => 'boolean',
        'verification_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function relatedTo(): MorphTo
    {
        return $this->morphTo();
    }
}
