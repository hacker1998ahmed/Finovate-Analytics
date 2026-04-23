<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_portal_id',
        'action', // login, logout, failed_login, password_change
        'ip_address',
        'user_agent',
        'success',
        'error_message',
        'logged_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'logged_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function portal(): BelongsTo
    {
        return $this->belongsTo(ClientPortal::class, 'client_portal_id');
    }
}
