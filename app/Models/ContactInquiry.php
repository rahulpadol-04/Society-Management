<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Platform-level contact inquiry (global, no tenant scope).
 */
class ContactInquiry extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'message',
        'society_name', 'status', 'notes',
    ];
}
