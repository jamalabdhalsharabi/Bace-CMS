<?php

declare(strict_types=1);

namespace Modules\Core\Application\Actions;

use Illuminate\Support\Facades\DB;

/**
 * Base Action Class.
 *
 * Provides common functionality for action classes.
 * Each action represents a single use case or operation.
 */
abstract class Action
{
    /**
     * Execute the action within a database transaction.
     *
     * @template T
     * @param callable(): T $callback The callback to execute
     * @return T The callback result
     * @throws \Throwable
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Get the authenticated user ID.
     *
     * @return string|null
     */
    protected function userId(): ?string
    {
        return request()->user()?->id;
    }
}
