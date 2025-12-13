<?php

declare(strict_types=1);

namespace Modules\Services\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Services\Contracts\ServiceServiceContract;
use Modules\Services\Domain\Models\Service;

/**
 * Class ServiceService
 *
 * Service class for managing business services including CRUD,
 * workflow, translations, media, categories, and revisions.
 *
 * @package Modules\Services\Services
 */
class ServiceService implements ServiceServiceContract
{
    /**
     * {@inheritdoc}
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Service::with(['translation', 'categories']);
        
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['is_featured'])) $query->where('is_featured', true);
        if (!empty($filters['category_id'])) {
            $query->whereHas('categories', fn($q) => $q->where('term_id', $filters['category_id']));
        }
        if (!empty($filters['search'])) {
            $query->whereHas('translations', fn($q) => 
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%")
            );
        }
        
        return $query->ordered()->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id): ?Service
    {
        return Service::with(['translations', 'categories', 'media', 'relatedServices'])->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySlug(string $slug): ?Service
    {
        return Service::whereHas('translations', fn($q) => $q->where('slug', $slug))
            ->with(['translations', 'categories', 'media'])
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Service
    {
        return DB::transaction(function () use ($data) {
            $service = Service::create([
                'slug' => $data['slug'],
                'status' => 'draft',
                'icon' => $data['icon'] ?? null,
                'color' => $data['color'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'created_by' => auth()->id(),
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $service->translations()->create(['locale' => $locale, ...$trans]);
                }
            }

            if (!empty($data['category_ids'])) {
                $service->categories()->sync($data['category_ids']);
            }

            $this->createRevision($service, 'created');

            return $service->fresh(['translations', 'categories']);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function update(Service $service, array $data): Service
    {
        return DB::transaction(function () use ($service, $data) {
            $service->update(array_filter([
                'slug' => $data['slug'] ?? null,
                'icon' => $data['icon'] ?? null,
                'color' => $data['color'] ?? null,
                'sort_order' => $data['sort_order'] ?? null,
                'updated_by' => auth()->id(),
            ], fn($v) => $v !== null));

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $service->translations()->updateOrCreate(['locale' => $locale], $trans);
                }
            }

            if (isset($data['category_ids'])) {
                $service->categories()->sync($data['category_ids']);
            }

            $this->createRevision($service, 'updated');

            return $service->fresh(['translations', 'categories']);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Service $service): bool
    {
        return $service->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function forceDelete(Service $service): bool
    {
        return $service->forceDelete();
    }

    /**
     * {@inheritdoc}
     */
    public function restore(string $id): ?Service
    {
        $service = Service::withTrashed()->find($id);
        $service?->restore();
        return $service;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDraft(Service $service, array $data): Service
    {
        $service->update(['status' => 'draft', ...$data]);
        $this->createRevision($service, 'draft_saved');
        return $service->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function submitForReview(Service $service): Service
    {
        $service->submitForReview();
        return $service;
    }

    /**
     * {@inheritdoc}
     */
    public function startReview(Service $service, string $reviewerId): Service
    {
        $service->startReview($reviewerId);
        return $service;
    }

    /**
     * {@inheritdoc}
     */
    public function approve(Service $service, ?string $notes = null): Service
    {
        $service->approve($notes);
        return $service;
    }

    /**
     * {@inheritdoc}
     */
    public function reject(Service $service, ?string $notes = null): Service
    {
        $service->reject($notes);
        return $service;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Service $service): Service
    {
        $service->publish();
        $this->indexInSearch($service);
        return $service;
    }

    /**
     * {@inheritdoc}
     */
    public function schedule(Service $service, \DateTime $date): Service
    {
        $service->schedule($date);
        return $service;
    }

    public function cancelSchedule(Service $service): Service
    {
        $service->cancelSchedule();
        return $service;
    }

    public function unpublish(Service $service): Service
    {
        $service->unpublish();
        $this->removeFromIndex($service);
        return $service;
    }

    public function archive(Service $service): Service
    {
        $service->archive();
        $this->removeFromIndex($service);
        return $service;
    }

    public function unarchive(Service $service): Service
    {
        $service->restore();
        return $service;
    }

    // Features
    public function feature(Service $service): Service
    {
        $service->feature();
        return $service;
    }

    public function unfeature(Service $service): Service
    {
        $service->unfeature();
        return $service;
    }

    public function clone(Service $service, string $newSlug): Service
    {
        return DB::transaction(function () use ($service, $newSlug) {
            $newService = $service->replicate(['id', 'slug', 'status', 'published_at']);
            $newService->slug = $newSlug;
            $newService->status = 'draft';
            $newService->save();

            foreach ($service->translations as $trans) {
                $newService->translations()->create(
                    $trans->only(['locale', 'name', 'slug', 'short_description', 'description', 'features', 'benefits', 'process', 'faq'])
                );
            }

            $newService->categories()->sync($service->categories->pluck('id'));

            return $newService->fresh(['translations', 'categories']);
        });
    }

    public function reorder(array $order): bool
    {
        return DB::transaction(function () use ($order) {
            foreach ($order as $index => $id) {
                Service::where('id', $id)->update(['sort_order' => $index]);
            }
            return true;
        });
    }

    // Translations
    public function createTranslation(Service $service, string $locale, array $data): Service
    {
        $service->translations()->updateOrCreate(['locale' => $locale], $data);
        return $service->fresh(['translations']);
    }

    public function reviewTranslation(Service $service, string $locale): Service
    {
        // Mark translation as reviewed
        return $service;
    }

    public function publishTranslation(Service $service, string $locale): Service
    {
        // Publish specific translation
        return $service;
    }

    // Media
    public function attachMedia(Service $service, array $mediaIds): Service
    {
        foreach ($mediaIds as $index => $mediaId) {
            $service->media()->updateOrCreate(
                ['media_id' => $mediaId],
                ['sort_order' => $index]
            );
        }
        return $service->fresh(['media']);
    }

    public function detachMedia(Service $service, array $mediaIds): Service
    {
        $service->media()->whereIn('media_id', $mediaIds)->delete();
        return $service->fresh(['media']);
    }

    public function reorderMedia(Service $service, array $order): Service
    {
        foreach ($order as $index => $mediaId) {
            $service->media()->where('media_id', $mediaId)->update(['sort_order' => $index]);
        }
        return $service->fresh(['media']);
    }

    // Taxonomies
    public function syncCategories(Service $service, array $termIds): Service
    {
        $service->categories()->sync($termIds);
        return $service->fresh(['categories']);
    }

    public function attachRelated(Service $service, array $serviceIds): Service
    {
        $service->relatedServices()->sync($serviceIds);
        return $service->fresh(['relatedServices']);
    }

    // Revisions
    public function getRevisions(Service $service): Collection
    {
        return $service->revisions()->latest()->get();
    }

    public function compareRevisions(Service $service, string $revisionId1, string $revisionId2): array
    {
        $rev1 = $service->revisions()->find($revisionId1);
        $rev2 = $service->revisions()->find($revisionId2);

        return [
            'revision_1' => $rev1?->data,
            'revision_2' => $rev2?->data,
            'diff' => [], // Implement diff logic
        ];
    }

    public function restoreRevision(Service $service, string $revisionId): Service
    {
        $revision = $service->revisions()->find($revisionId);
        if ($revision && $revision->data) {
            $service->update($revision->data);
        }
        return $service->fresh();
    }

    // Search
    public function indexInSearch(Service $service): bool
    {
        // Implement search indexing
        return true;
    }

    public function removeFromIndex(Service $service): bool
    {
        // Implement search removal
        return true;
    }

    protected function createRevision(Service $service, string $action): void
    {
        if (config('services.revisions.enabled', true)) {
            $service->revisions()->create([
                'user_id' => auth()->id(),
                'action' => $action,
                'data' => $service->toArray(),
            ]);
        }
    }
}
