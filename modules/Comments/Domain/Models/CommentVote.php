<?php

declare(strict_types=1);

namespace Modules\Comments\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CommentVote Model - Tracks user votes on comments.
 *
 * This model stores upvotes and downvotes on comments,
 * ensuring one vote per user per comment.
 *
 * @property string $id UUID primary key
 * @property string $comment_id Foreign key to comments table
 * @property string $user_id Foreign key to users table
 * @property int $vote Vote value (1 = upvote, -1 = downvote)
 * @property \Carbon\Carbon $created_at Record creation timestamp
 *
 * @property-read Comment $comment The voted comment
 * @property-read \App\Models\User $user The voting user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CommentVote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentVote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CommentVote query()
 */
class CommentVote extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'comment_votes';

    protected $fillable = [
        'comment_id',
        'user_id',
        'vote',
        'created_at',
    ];

    protected $casts = [
        'vote' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the voted comment.
     *
     * @return BelongsTo<Comment, CommentVote>
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the voting user.
     *
     * @return BelongsTo<\App\Models\User, CommentVote>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Check if this is an upvote.
     *
     * @return bool
     */
    public function isUpvote(): bool
    {
        return $this->vote > 0;
    }

    /**
     * Check if this is a downvote.
     *
     * @return bool
     */
    public function isDownvote(): bool
    {
        return $this->vote < 0;
    }
}
