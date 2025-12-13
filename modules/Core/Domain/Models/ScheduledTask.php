<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * ScheduledTask Model - Manages scheduled/cron jobs.
 *
 * This model stores configurable scheduled tasks that can be
 * managed through the admin panel.
 *
 * @property string $id UUID primary key
 * @property string $name Task display name
 * @property string $command Artisan command to run
 * @property array|null $parameters Command parameters
 * @property string $cron_expression Cron schedule expression
 * @property string $timezone Task timezone
 * @property bool $is_active Whether task is enabled
 * @property \Carbon\Carbon|null $last_run_at Last execution time
 * @property \Carbon\Carbon|null $next_run_at Next scheduled run
 * @property string|null $last_run_status Last run status
 * @property int|null $last_run_duration Last run duration in seconds
 * @property string|null $last_run_output Last run output/error
 * @property int $run_count Total successful runs
 * @property int $failure_count Total failed runs
 * @property bool $notify_on_failure Whether to notify on failure
 * @property string|null $notify_emails Notification email addresses
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledTask active() Filter active tasks
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledTask due() Filter tasks due to run
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ScheduledTask query()
 */
class ScheduledTask extends Model
{
    use HasUuids;

    protected $table = 'scheduled_tasks';

    protected $fillable = [
        'name',
        'command',
        'parameters',
        'cron_expression',
        'timezone',
        'is_active',
        'last_run_at',
        'next_run_at',
        'last_run_status',
        'last_run_duration',
        'last_run_output',
        'run_count',
        'failure_count',
        'notify_on_failure',
        'notify_emails',
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'last_run_duration' => 'integer',
        'run_count' => 'integer',
        'failure_count' => 'integer',
        'notify_on_failure' => 'boolean',
    ];

    /**
     * Scope to filter active tasks.
     *
     * @param \Illuminate\Database\Eloquent\Builder<ScheduledTask> $query
     * @return \Illuminate\Database\Eloquent\Builder<ScheduledTask>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter tasks due to run.
     *
     * @param \Illuminate\Database\Eloquent\Builder<ScheduledTask> $query
     * @return \Illuminate\Database\Eloquent\Builder<ScheduledTask>
     */
    public function scopeDue($query)
    {
        return $query->active()->where('next_run_at', '<=', now());
    }
}
