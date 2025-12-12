<?php

declare(strict_types=1);

namespace Modules\Content\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Content\Domain\Models\Revision;

trait HasRevisions
{
    public static function bootHasRevisions(): void
    {
        static::updating(function ($model) {
            if ($model->shouldCreateRevision()) {
                $model->createRevision();
            }
        });
    }

    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable')->orderByDesc('revision_number');
    }

    public function createRevision(?string $summary = null, bool $isAuto = true): Revision
    {
        $lastRevision = $this->revisions()->max('revision_number') ?? 0;

        return $this->revisions()->create([
            'user_id' => auth()->id(),
            'revision_number' => $lastRevision + 1,
            'data' => $this->getRevisionData(),
            'changes' => $this->getRevisionChanges(),
            'summary' => $summary,
            'is_auto' => $isAuto,
        ]);
    }

    public function restoreRevision(Revision $revision): bool
    {
        $data = $revision->data;
        unset($data['id'], $data['created_at'], $data['updated_at']);
        
        $this->fill($data);
        return $this->save();
    }

    public function getRevision(int $number): ?Revision
    {
        return $this->revisions()->where('revision_number', $number)->first();
    }

    protected function getRevisionData(): array
    {
        return $this->toArray();
    }

    protected function getRevisionChanges(): array
    {
        $changes = [];
        foreach ($this->getDirty() as $field => $newValue) {
            $changes[$field] = [
                'old' => $this->getOriginal($field),
                'new' => $newValue,
            ];
        }
        return $changes;
    }

    protected function shouldCreateRevision(): bool
    {
        return !empty($this->getDirty());
    }
}
