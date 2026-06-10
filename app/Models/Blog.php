<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Platform-level blog post (global, no tenant scope).
 */
class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'cover_image',
        'author_id', 'category', 'status', 'published_at', 'views',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'views'        => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->withoutGlobalScopes();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
