<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceTemplate extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'society_id', 'name', 'header_html', 'footer_html', 'terms', 'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }
}
