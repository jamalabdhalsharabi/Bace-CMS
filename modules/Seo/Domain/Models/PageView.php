<?php

declare(strict_types=1);

namespace Modules\Seo\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * PageView Model - Tracks page views and visitor analytics.
 *
 * This model stores detailed page view data for analytics including
 * visitor info, device data, location, and UTM parameters.
 *
 * @property string $id UUID primary key
 * @property string $url Visited URL
 * @property string|null $viewable_type Polymorphic model type
 * @property string|null $viewable_id UUID of viewed entity
 * @property string|null $visitor_id Unique visitor identifier
 * @property string|null $user_id Foreign key to users (if authenticated)
 * @property string|null $session_id Session identifier
 * @property string|null $referrer Referring URL
 * @property string|null $utm_source UTM source parameter
 * @property string|null $utm_medium UTM medium parameter
 * @property string|null $utm_campaign UTM campaign parameter
 * @property string|null $utm_term UTM term parameter
 * @property string|null $utm_content UTM content parameter
 * @property string|null $device_type Device type (desktop, mobile, tablet)
 * @property string|null $browser Browser name
 * @property string|null $browser_version Browser version
 * @property string|null $os Operating system
 * @property string|null $os_version OS version
 * @property string|null $country Country code (2 chars)
 * @property string|null $city City name
 * @property string|null $ip_address Visitor IP address
 * @property string|null $user_agent Full user agent string
 * @property \Carbon\Carbon $created_at Record creation timestamp
 *
 * @property-read Model|null $viewable Viewed entity (polymorphic)
 * @property-read \App\Models\User|null $user Visiting user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PageView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PageView query()
 */
class PageView extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'page_views';

    protected $fillable = [
        'url',
        'viewable_type',
        'viewable_id',
        'visitor_id',
        'user_id',
        'session_id',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'device_type',
        'browser',
        'browser_version',
        'os',
        'os_version',
        'country',
        'city',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the viewed entity.
     *
     * @return MorphTo<Model, PageView>
     */
    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the visiting user.
     *
     * @return BelongsTo<\App\Models\User, PageView>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }
}
