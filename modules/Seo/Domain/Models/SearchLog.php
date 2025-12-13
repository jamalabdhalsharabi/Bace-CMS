<?php

declare(strict_types=1);

namespace Modules\Seo\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SearchLog Model - Tracks search queries and results.
 *
 * This model stores search query data for analytics and
 * improving search functionality.
 *
 * @property string $id UUID primary key
 * @property string $query The search query text
 * @property int $results_count Number of results found
 * @property string|null $user_id Foreign key to users (if authenticated)
 * @property string|null $session_id Session identifier
 * @property string|null $locale Search locale
 * @property array|null $filters Applied search filters
 * @property string|null $ip_address Searcher IP address
 * @property \Carbon\Carbon $created_at Record creation timestamp
 *
 * @property-read \App\Models\User|null $user Searching user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SearchLog noResults() Filter zero-result searches
 * @method static \Illuminate\Database\Eloquent\Builder|SearchLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchLog query()
 */
class SearchLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'search_logs';

    protected $fillable = [
        'query',
        'results_count',
        'user_id',
        'session_id',
        'locale',
        'filters',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'results_count' => 'integer',
        'filters' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the searching user.
     *
     * @return BelongsTo<\App\Models\User, SearchLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Scope to filter zero-result searches.
     *
     * @param \Illuminate\Database\Eloquent\Builder<SearchLog> $query
     * @return \Illuminate\Database\Eloquent\Builder<SearchLog>
     */
    public function scopeNoResults($query)
    {
        return $query->where('results_count', 0);
    }
}
