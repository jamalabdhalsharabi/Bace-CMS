<?php

declare(strict_types=1);

namespace Modules\Comments\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'comments';

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'parent_id',
        'user_id',
        'author_name',
        'author_email',
        'content',
        'status',
        'ip_address',
        'user_agent',
        'likes_count',
        'is_pinned',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'likes_count' => 'integer',
        'is_pinned' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'approved_by');
    }

    public function getAuthorNameAttribute($value): string
    {
        return $this->user?->full_name ?? $value ?? 'Guest';
    }

    public function getAuthorEmailAttribute($value): ?string
    {
        return $this->user?->email ?? $value;
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSpam(): bool
    {
        return $this->status === 'spam';
    }

    public function approve(?string $approvedBy = null): self
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy ?? auth()->id(),
        ]);

        return $this;
    }

    public function reject(): self
    {
        $this->update(['status' => 'rejected']);
        return $this;
    }

    public function markAsSpam(): self
    {
        $this->update(['status' => 'spam']);
        return $this;
    }

    public function pin(): self
    {
        $this->update(['is_pinned' => true]);
        return $this;
    }

    public function unpin(): self
    {
        $this->update(['is_pinned' => false]);
        return $this;
    }

    public function incrementLikes(): self
    {
        $this->increment('likes_count');
        return $this;
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeForModel($query, string $type, string $id)
    {
        return $query->where('commentable_type', $type)->where('commentable_id', $id);
    }
}
