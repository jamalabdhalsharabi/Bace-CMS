<?php

declare(strict_types=1);

namespace Modules\Comments\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CommentReport Model - Tracks user reports on comments.
 *
 * This model stores reports submitted by users for inappropriate
 * comments, with moderation workflow support.
 *
 * @property string $id UUID primary key
 * @property string $comment_id Foreign key to comments table
 * @property string|null $user_id Foreign key to users table (null for guests)
 * @property string $reason Report reason category
 * @property string|null $details Additional report details
 * @property string $status Report status (pending, reviewed, dismissed, actioned)
 * @property string|null $reviewed_by UUID of reviewing moderator
 * @property \Carbon\Carbon|null $reviewed_at When report was reviewed
 * @property string|null $review_notes Moderator review notes
 * @property string|null $ip_address Reporter's IP address
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Comment $comment The reported comment
 * @property-read \App\Models\User|null $user The reporting user
 * @property-read \App\Models\User|null $reviewer The reviewing moderator
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport pending() Filter pending reports
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentReport query()
 */
class CommentReport extends Model
{
    use HasUuids;

    protected $table = 'comment_reports';

    protected $fillable = [
        'comment_id',
        'user_id',
        'reason',
        'details',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'ip_address',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the reported comment.
     *
     * @return BelongsTo<Comment, CommentReport>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the reporting user.
     *
     * @return BelongsTo<\App\Models\User, CommentReport>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Get the reviewing moderator.
     *
     * @return BelongsTo<\App\Models\User, CommentReport>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'reviewed_by');
    }

    /**
     * Mark report as reviewed.
     *
     * @param string $status New status
     * @param string|null $notes Review notes
     * @param string|null $reviewerId UUID of reviewer
     * @return self
     */
    public function markAsReviewed(string $status, ?string $notes = null, ?string $reviewerId = null): self
    {
        $this->update([
            'status' => $status,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId ?? request()->user()?->id,
            'review_notes' => $notes,
        ]);
        return $this;
    }

    /**
     * Scope to filter pending reports.
     *
     * @param \Illuminate\Database\Eloquent\Builder<CommentReport> $query
     * @return \Illuminate\Database\Eloquent\Builder<CommentReport>
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
