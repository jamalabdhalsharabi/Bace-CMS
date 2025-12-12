<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait HasRevisions
{
    /**
     * Boot the trait.
     */
    public static function bootHasRevisions(): void
    {
        static::updating(function ($model) {
            if ($model->shouldCreateRevision()) {
                $model->createRevision();
            }
        });
    }

    /**
     * Get all revisions.
     */
    public function revisions(): MorphMany
    {
        return $this->morphMany($this->getRevisionModelClass(), 'revisionable')
            ->orderByDesc('created_at');
    }

    /**
     * Create a new revision.
     */
    public function createRevision(?string $summary = null, bool $isAuto = true): mixed
    {
        $revisionClass = $this->getRevisionModelClass();

        $revision = new $revisionClass([
            'revisionable_type' => get_class($this),
            'revisionable_id' => $this->id,
            'user_id' => Auth::id(),
            'revision_number' => $this->getNextRevisionNumber(),
            'data' => $this->getRevisionData(),
            'changes' => $this->getRevisionChanges(),
            'summary' => $summary,
            'is_auto' => $isAuto,
        ]);

        $revision->save();

        return $revision;
    }

    /**
     * Get revision data (full snapshot).
     */
    protected function getRevisionData(): array
    {
        $attributes = $this->getOriginal();

        // Exclude non-revisable attributes
        foreach ($this->getNonRevisionableAttributes() as $attr) {
            unset($attributes[$attr]);
        }

        return $attributes;
    }

    /**
     * Get revision changes (diff only).
     */
    protected function getRevisionChanges(): array
    {
        $changes = [];

        foreach ($this->getDirty() as $key => $newValue) {
            if (in_array($key, $this->getNonRevisionableAttributes())) {
                continue;
            }

            $changes[$key] = [
                'old' => $this->getOriginal($key),
                'new' => $newValue,
            ];
        }

        return $changes;
    }

    /**
     * Get next revision number.
     */
    protected function getNextRevisionNumber(): int
    {
        return (int) $this->revisions()->max('revision_number') + 1;
    }

    /**
     * Check if should create revision.
     */
    protected function shouldCreateRevision(): bool
    {
        // Don't create if only non-revisable attributes changed
        $dirty = array_keys($this->getDirty());
        $revisable = array_diff($dirty, $this->getNonRevisionableAttributes());

        return !empty($revisable);
    }

    /**
     * Restore to specific revision.
     */
    public function restoreRevision(int $revisionNumber): static
    {
        $revision = $this->revisions()
            ->where('revision_number', $revisionNumber)
            ->firstOrFail();

        // Create revision of current state before restore
        $this->createRevision('Before restore to revision #' . $revisionNumber, false);

        // Restore attributes
        $this->fill($revision->data);
        $this->save();

        return $this;
    }

    /**
     * Get latest revision.
     */
    public function getLatestRevision(): mixed
    {
        return $this->revisions()->first();
    }

    /**
     * Get revision by number.
     */
    public function getRevision(int $number): mixed
    {
        return $this->revisions()->where('revision_number', $number)->first();
    }

    /**
     * Compare two revisions.
     */
    public function compareRevisions(int $from, int $to): array
    {
        $fromRevision = $this->getRevision($from);
        $toRevision = $this->getRevision($to);

        if (!$fromRevision || !$toRevision) {
            return [];
        }

        $diff = [];
        $allKeys = array_unique(array_merge(
            array_keys($fromRevision->data ?? []),
            array_keys($toRevision->data ?? [])
        ));

        foreach ($allKeys as $key) {
            $fromValue = $fromRevision->data[$key] ?? null;
            $toValue = $toRevision->data[$key] ?? null;

            if ($fromValue !== $toValue) {
                $diff[$key] = [
                    'from' => $fromValue,
                    'to' => $toValue,
                ];
            }
        }

        return $diff;
    }

    /**
     * Get non-revisable attributes.
     */
    protected function getNonRevisionableAttributes(): array
    {
        return $this->nonRevisionable ?? [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
            'remember_token',
        ];
    }

    /**
     * Get revision model class.
     */
    protected function getRevisionModelClass(): string
    {
        return $this->revisionModel ?? 'Modules\\Core\\Domain\\Models\\Revision';
    }

    /**
     * Prune old revisions.
     */
    public function pruneRevisions(int $keep = 10): int
    {
        $toDelete = $this->revisions()
            ->orderByDesc('revision_number')
            ->skip($keep)
            ->pluck('id');

        return $this->revisions()->whereIn('id', $toDelete)->delete();
    }
}
