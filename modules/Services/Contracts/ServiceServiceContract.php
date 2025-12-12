<?php

declare(strict_types=1);

namespace Modules\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Services\Domain\Models\Service;

interface ServiceServiceContract
{
    // CRUD
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function find(string $id): ?Service;
    public function findBySlug(string $slug): ?Service;
    public function create(array $data): Service;
    public function update(Service $service, array $data): Service;
    public function delete(Service $service): bool;
    public function forceDelete(Service $service): bool;
    public function restore(string $id): ?Service;

    // Workflow
    public function saveDraft(Service $service, array $data): Service;
    public function submitForReview(Service $service): Service;
    public function startReview(Service $service, string $reviewerId): Service;
    public function approve(Service $service, ?string $notes = null): Service;
    public function reject(Service $service, ?string $notes = null): Service;
    public function publish(Service $service): Service;
    public function schedule(Service $service, \DateTime $date): Service;
    public function cancelSchedule(Service $service): Service;
    public function unpublish(Service $service): Service;
    public function archive(Service $service): Service;
    public function unarchive(Service $service): Service;

    // Features
    public function feature(Service $service): Service;
    public function unfeature(Service $service): Service;
    public function clone(Service $service, string $newSlug): Service;
    public function reorder(array $order): bool;

    // Translations
    public function createTranslation(Service $service, string $locale, array $data): Service;
    public function reviewTranslation(Service $service, string $locale): Service;
    public function publishTranslation(Service $service, string $locale): Service;

    // Media
    public function attachMedia(Service $service, array $mediaIds): Service;
    public function detachMedia(Service $service, array $mediaIds): Service;
    public function reorderMedia(Service $service, array $order): Service;

    // Taxonomies
    public function syncCategories(Service $service, array $termIds): Service;
    public function attachRelated(Service $service, array $serviceIds): Service;

    // Revisions
    public function getRevisions(Service $service): Collection;
    public function compareRevisions(Service $service, string $revisionId1, string $revisionId2): array;
    public function restoreRevision(Service $service, string $revisionId): Service;

    // Search
    public function indexInSearch(Service $service): bool;
    public function removeFromIndex(Service $service): bool;
}
