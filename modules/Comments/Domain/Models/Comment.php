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
 * Comment Model - Represents user comments on various content types.
 *
 * This model handles polymorphic comments with threading support,
 * moderation workflow, spam detection, and voting capabilities.
 *
 * @property string $id UUID primary key
 * @property string $commentable_type Polymorphic model type (e.g., 'Article', 'Product')
 * @property string $commentable_id UUID of the commented entity
 * @property string|null $parent_id Foreign key to parent comment (for replies)
 * @property int $depth Nesting depth (0 = top-level)
 * @property string $content Comment text content
 * @property string|null $user_id Foreign key to user (null for guests)
 * @property string|null $author_name Guest author name
 * @property string|null $author_email Guest author email
 * @property string $status Moderation status (pending, approved, rejected, spam, hidden)
 * @property bool $is_spam Whether flagged as spam
 * @property float|null $spam_score Spam detection score (0-100)
 * @property int $upvotes Number of upvotes
 * @property int $downvotes Number of downvotes
 * @property int $report_count Number of user reports
 * @property bool $is_pinned Whether pinned to top
 * @property bool $is_locked Whether comments are locked (prevents new replies)
 * @property string|null $ip_address Commenter's IP address
 * @property string|null $user_agent Commenter's browser user agent
 * @property \Carbon\Carbon|null $approved_at When comment was approved
 * @property string|null $approved_by UUID of approving moderator
 * @property \Carbon\Carbon|null $edited_at When comment was last edited
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read Model $commentable The commented entity (polymorphic)
 * @property-read Comment|null $parent Parent comment (if reply)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Comment> $replies Direct replies
 * @property-read \App\Models\User|null $user Comment author (if authenticated)
 * @property-read \App\Models\User|null $approver Moderator who approved
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Comment approved() Filter approved comments
 * @method static \Illuminate\Database\Eloquent\Builder|Comment pending() Filter pending comments
 * @method static \Illuminate\Database\Eloquent\Builder|Comment root() Filter top-level comments
 * @method static \Illuminate\Database\Eloquent\Builder|Comment pinned() Filter pinned comments
 * @method static \Illuminate\Database\Eloquent\Builder|Comment forModel(string $type, string $id) Filter by entity
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment query()
 */
class Comment extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'comments';

    protected $fillable = [
        'commentable_type',
        'commentable_id',
        'parent_id',
        'depth',
        'content',
        'user_id',
        'author_name',
        'author_email',
        'status',
        'is_spam',
        'spam_score',
        'upvotes',
        'downvotes',
        'report_count',
        'is_pinned',
        'is_locked',
        'ip_address',
        'user_agent',
        'approved_at',
        'approved_by',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'is_spam' => 'boolean',
            'spam_score' => 'decimal:2',
            'upvotes' => 'integer',
            'downvotes' => 'integer',
            'report_count' => 'integer',
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
            'approved_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

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
     * Get the moderator who approved this comment.
     *
     * @return BelongsTo<\App\Models\User, Comment>
     */
    public function approver(): BelongsTo
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
            'approved_by' => $approvedBy ?? request()->user()?->id,
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
     * Increment the comment's upvote counter.
     *
     * Atomically increases the upvotes field by one.
     * Used when users upvote a comment.
     *
     * @return self The current Comment instance for method chaining
     */
    public function incrementUpvotes(): self
    {
        $this->increment('upvotes');
        return $this;
    }

    /**
     * Increment the comment's downvote counter.
     *
     * Atomically increases the downvotes field by one.
     * Used when users downvote a comment.
     *
     * @return self The current Comment instance for method chaining
     */
    public function incrementDownvotes(): self
    {
        $this->increment('downvotes');
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
