<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class DocumentTag extends Model
{
    protected $fillable = [
        'name',
        'color',
    ];

    public function internalDocuments(): MorphToMany
    {
        return $this->morphedByMany(InternalDocument::class, 'taggable');
    }

    public function auditDocuments(): MorphToMany
    {
        return $this->morphedByMany(AuditDocument::class, 'taggable');
    }
}
