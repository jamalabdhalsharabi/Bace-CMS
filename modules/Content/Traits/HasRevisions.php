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
        return $this->morphMany(Revision::class, 'revisionable')->orderByDesc('version');
    }

    public function createRevision(?string $summary = null, string $type = 'update'): Revision
    {
        $lastVersion = $this->revisions()->max('version') ?? 0;
        $oldData = $this->getOriginal();

        return $this->revisions()->create([
            'created_by' => auth()->id(),
            'version' => $lastVersion + 1,
            'type' => $type,
            'old_data' => $oldData,
            'new_data' => $this->getRevisionData(),
            'diff' => $this->getRevisionChanges(),
            'summary' => $summary,
        ]);
    }

    public function restoreRevision(Revision $revision): bool
    {
        $data = $revision->new_data;
        unset($data['id'], $data['created_at'], $data['updated_at']);
        
        $this->fill($data);
        return $this->save();
    }

    public function getRevision(int $version): ?Revision
    {
        return $this->revisions()->where('version', $version)->first();
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
