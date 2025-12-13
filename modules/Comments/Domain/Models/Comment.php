<?php

declare(strict_types=1);

namespace Modules\Comments\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Comment
 *
 * Eloquent model representing a comment on any commentable entity
 * with moderation, replies, and user attribution.
 *
 * @package Modules\Comments\Domain\Models
 *
 * @property string $id
 * @property string $commentable_id
 * @property string $commentable_type
 * @property string|null $parent_id
 * @property string|null $user_id
 * @property string|null $author_name
 * @property string|null $author_email
 * @property string $content
 * @property string $status
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property int $likes_count
 * @property bool $is_pinned
 * @property \Carbon\Carbon|null $approved_at
 * @property string|null $approved_by
 *
 * @property-read Model $commentable
 * @property-read Comment|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|Comment[] $replies
 * @property-read \Modules\Users\Domain\Models\User|null $user
 */
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

    /**
     * Define the polymorphic relationship to the commentable entity.
     *
     * Retrieves the model instance (Article, Product, etc.) that this
     * comment is attached to. Enables commenting on any model type.
     *
     * @return MorphTo The morph-to relationship instance
     */
    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Define the belongs-to relationship with the parent comment.
     *
     * Retrieves the parent comment if this is a reply.
     * Returns null for top-level comments.
     *
     * @return BelongsTo The belongs-to relationship instance to Comment
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Define the has-many relationship with comment replies.
     *
     * Retrieves all direct replies to this comment ordered by
     * creation date (oldest first) for threaded display.
     *
     * @return HasMany The has-many relationship instance to Comment
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    /**
     * Define the belongs-to relationship with the comment author.
     *
     * Retrieves the User model who posted this comment.
     * May be null for guest comments.
     *
     * @return BelongsTo The belongs-to relationship instance to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Define the belongs-to relationship with the moderator who approved.
     *
     * Retrieves the User model who approved this comment.
     * Used for moderation audit trails.
     *
     * @return BelongsTo The belongs-to relationship instance to User
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'approved_by');
    }

    /**
     * Accessor for the comment author's display name.
     *
     * Returns the authenticated user's full name if available,
     * otherwise returns the guest author_name or 'Guest' as fallback.
     *
     * @param string|null $value The raw author_name value
     *
     * @return string The display name for the comment author
     */
    public function getAuthorNameAttribute($value): string
    {
        return $this->user?->full_name ?? $value ?? 'Guest';
    }

    /**
     * Accessor for the comment author's email address.
     *
     * Returns the authenticated user's email if available,
     * otherwise returns the guest author_email.
     *
     * @param string|null $value The raw author_email value
     *
     * @return string|null The email address or null if not available
     */
    public function getAuthorEmailAttribute($value): ?string
    {
        return $this->user?->email ?? $value;
    }

    /**
     * Determine if the comment has been approved.
     *
     * Checks if the status field equals 'approved'.
     * Approved comments are visible to the public.
     *
     * @return bool True if the comment is approved, false otherwise
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Determine if the comment is pending moderation.
     *
     * Checks if the status field equals 'pending'.
     * Pending comments await moderator review.
     *
     * @return bool True if the comment is pending, false otherwise
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Determine if the comment has been marked as spam.
     *
     * Checks if the status field equals 'spam'.
     * Spam comments are hidden from public view.
     *
     * @return bool True if the comment is spam, false otherwise
     */
    public function isSpam(): bool
    {
        return $this->status === 'spam';
    }

    /**
     * Approve the comment for public display.
     *
     * Sets the status to 'approved' and records the approval
     * timestamp and approving user for audit purposes.
     *
     * @param string|null $approvedBy The UUID of the approving user, defaults to current user
     *
     * @return self The current Comment instance for method chaining
     */
    public function approve(?string $approvedBy = null): self
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy ?? auth()->id(),
        ]);

        return $this;
    }

    /**
     * Reject the comment.
     *
     * Sets the status to 'rejected'. Rejected comments
     * are not displayed publicly.
     *
     * @return self The current Comment instance for method chaining
     */
    public function reject(): self
    {
        $this->update(['status' => 'rejected']);
        return $this;
    }

    /**
     * Mark the comment as spam.
     *
     * Sets the status to 'spam'. Spam comments are hidden
     * and may be used for training spam filters.
     *
     * @return self The current Comment instance for method chaining
     */
    public function markAsSpam(): self
    {
        $this->update(['status' => 'spam']);
        return $this;
    }

    /**
     * Pin the comment to the top of the list.
     *
     * Sets is_pinned to true. Pinned comments appear
     * prominently at the top of comment threads.
     *
     * @return self The current Comment instance for method chaining
     */
    public function pin(): self
    {
        $this->update(['is_pinned' => true]);
        return $this;
    }

    /**
     * Remove the pinned status from the comment.
     *
     * Sets is_pinned to false. The comment returns to
     * normal chronological ordering.
     *
     * @return self The current Comment instance for method chaining
     */
    public function unpin(): self
    {
        $this->update(['is_pinned' => false]);
        return $this;
    }

    /**
     * Increment the comment's like counter.
     *
     * Atomically increases the likes_count field by one.
     * Used when users upvote or like a comment.
     *
     * @return self The current Comment instance for method chaining
     */
    public function incrementLikes(): self
    {
        $this->increment('likes_count');
        return $this;
    }

    /**
     * Query scope to filter only approved comments.
     *
     * Filters comments with 'approved' status for public display.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Query scope to filter only pending comments.
     *
     * Filters comments with 'pending' status for moderation queue.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Query scope to filter only top-level comments.
     *
     * Filters comments with no parent (not replies).
     * Used for displaying the main comment thread.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Query scope to filter only pinned comments.
     *
     * Filters comments where is_pinned flag is true.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Query scope to filter comments for a specific model.
     *
     * Filters comments by their polymorphic commentable type and ID.
     * Used to retrieve all comments for a given article, product, etc.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     * @param string $type The fully qualified model class name
     * @param string $id The UUID of the commentable model
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeForModel($query, string $type, string $id)
    {
        return $query->where('commentable_type', $type)->where('commentable_id', $id);
    }
}
