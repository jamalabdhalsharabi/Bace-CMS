<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Content\Domain\Models\Page;
use Modules\Core\Domain\Repositories\BaseRepository;

/**
 * Page Repository.
 *
 * @extends BaseRepository<Page>
 */
final class PageRepository extends BaseRepository
{
    public function __construct(Page $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['template'])) {
            $query->where('template', $filters['template']);
        }

        if (isset($filters['parent_id'])) {
            if ($filters['parent_id'] === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $filters['parent_id']);
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('translations', fn ($q) => 
                $q->where('title', 'LIKE', "%{$search}%")
            );
        }

        return $query->ordered()->paginate($perPage);
    }

    public function getPublished(): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->ordered()
            ->get();
    }

    public function getMenuPages(): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->where('show_in_menu', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('show_in_menu', true)])
            ->ordered()
            ->get();
    }

    public function findBySlug(string $slug, ?string $locale = null): ?Page
    {
        $locale = $locale ?? app()->getLocale();

        return $this->query()
            ->whereHas('translations', fn ($q) => 
                $q->where('slug', $slug)->where('locale', $locale)
            )
            ->first();
    }

    public function getTree(): Collection
    {
        return $this->query()
            ->whereNull('parent_id')
            ->with(['children.children'])
            ->ordered()
            ->get();
    }
}
