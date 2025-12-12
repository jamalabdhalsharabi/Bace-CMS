<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Modules\Core\Domain\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            ActivityLog::log('created', $model, class_basename($model) . ' created');
        });

        static::updated(function ($model) {
            ActivityLog::log('updated', $model, class_basename($model) . ' updated', [
                'changes' => $model->getChanges(),
            ]);
        });

        static::deleted(function ($model) {
            ActivityLog::log('deleted', $model, class_basename($model) . ' deleted');
        });
    }

    public function activityLogs()
    {
        return ActivityLog::forSubject($this)->latest('created_at')->get();
    }
}
