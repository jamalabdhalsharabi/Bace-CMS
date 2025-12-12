<?php

declare(strict_types=1);

namespace Modules\Content\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Content\Contracts\PageServiceContract;
use Modules\Content\Domain\Models\Page;

class PageService implements PageServiceContract
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Page::with(['author', 'featuredImage', 'translation']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        if (isset($filters['root']) && $filters['root']) {
            $query->whereNull('parent_id');
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('translations', fn ($q) => 
                $q->where('title', 'LIKE', "%{$search}%")
            );
        }

        return $query->ordered()->paginate($perPage);
    }

    public function getTree(): Collection
    {
        return Page::with(['children.children', 'translation'])
            ->whereNull('parent_id')
            ->ordered()
            ->get();
    }

    public function find(string $id): ?Page
    {
        return Page::with(['author', 'featuredImage', 'translations', 'parent', 'children'])->find($id);
    }

    public function findBySlug(string $slug, ?string $locale = null): ?Page
    {
        return Page::findBySlug($slug, $locale)?->load(['author', 'featuredImage', 'translations']);
    }

    public function create(array $data): Page
    {
        return DB::transaction(function () use ($data) {
            $page = Page::create([
                'parent_id' => $data['parent_id'] ?? null,
                'author_id' => $data['author_id'] ?? auth()->id(),
                'featured_image_id' => $data['featured_image_id'] ?? null,
                'template' => $data['template'] ?? 'default',
                'status' => $data['status'] ?? 'draft',
                'is_homepage' => $data['is_homepage'] ?? false,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $page->translations()->create([
                        'locale' => $locale,
                        'title' => $trans['title'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                        'content' => $trans['content'] ?? null,
                        'meta_title' => $trans['meta_title'] ?? null,
                        'meta_description' => $trans['meta_description'] ?? null,
                        'meta_keywords' => $trans['meta_keywords'] ?? null,
                    ]);
                }
            }

            if ($page->is_homepage) {
                Page::where('id', '!=', $page->id)->update(['is_homepage' => false]);
            }

            return $page->fresh(['author', 'featuredImage', 'translations']);
        });
    }

    public function update(Page $page, array $data): Page
    {
        return DB::transaction(function () use ($page, $data) {
            $page->update([
                'parent_id' => $data['parent_id'] ?? $page->parent_id,
                'featured_image_id' => $data['featured_image_id'] ?? $page->featured_image_id,
                'template' => $data['template'] ?? $page->template,
                'is_homepage' => $data['is_homepage'] ?? $page->is_homepage,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $page->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'title' => $trans['title'],
                            'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                            'content' => $trans['content'] ?? null,
                            'meta_title' => $trans['meta_title'] ?? null,
                            'meta_description' => $trans['meta_description'] ?? null,
                            'meta_keywords' => $trans['meta_keywords'] ?? null,
                        ]
                    );
                }
            }

            if ($page->is_homepage) {
                Page::where('id', '!=', $page->id)->update(['is_homepage' => false]);
            }

            return $page->fresh(['author', 'featuredImage', 'translations']);
        });
    }

    public function publish(Page $page): Page
    {
        $page->update([
            'status' => 'published',
            'published_at' => $page->published_at ?? now(),
        ]);

        return $page->fresh();
    }

    public function delete(Page $page): bool
    {
        Page::where('parent_id', $page->id)->update(['parent_id' => $page->parent_id]);

        return $page->delete();
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            Page::where('id', $id)->update(['ordering' => $index + 1]);
        }
    }

    public function setAsHomepage(Page $page): Page
    {
        Page::where('is_homepage', true)->update(['is_homepage' => false]);
        $page->update(['is_homepage' => true]);

        return $page->fresh();
    }

    public function forceDelete(Page $page): bool
    {
        return $page->forceDelete();
    }

    public function restore(string $id): ?Page
    {
        $page = Page::withTrashed()->find($id);
        $page?->restore();
        return $page;
    }

    public function saveDraft(Page $page, array $data): Page
    {
        $data['status'] = 'draft';
        return $this->update($page, $data);
    }

    public function submitForReview(Page $page): Page
    {
        $page->update(['status' => 'pending_review', 'submitted_at' => now()]);
        return $page->fresh();
    }

    public function approve(Page $page, ?string $notes = null): Page
    {
        $page->update(['status' => 'approved', 'review_notes' => $notes]);
        return $page->fresh();
    }

    public function reject(Page $page, ?string $notes = null): Page
    {
        $page->update(['status' => 'rejected', 'review_notes' => $notes]);
        return $page->fresh();
    }

    public function schedule(Page $page, \DateTime $date): Page
    {
        $page->update(['status' => 'scheduled', 'scheduled_at' => $date]);
        return $page->fresh();
    }

    public function cancelSchedule(Page $page): Page
    {
        $page->update(['status' => 'draft', 'scheduled_at' => null]);
        return $page->fresh();
    }

    public function unpublish(Page $page): Page
    {
        $page->update(['status' => 'unpublished', 'published_at' => null]);
        return $page->fresh();
    }

    public function archive(Page $page): Page
    {
        $page->update(['status' => 'archived', 'archived_at' => now()]);
        return $page->fresh();
    }

    public function unarchive(Page $page): Page
    {
        $page->update(['status' => 'draft', 'archived_at' => null]);
        return $page->fresh();
    }

    public function move(Page $page, ?string $parentId): Page
    {
        $page->update(['parent_id' => $parentId]);
        return $page->fresh();
    }

    public function setAs404(Page $page): Page
    {
        Page::where('is_404', true)->update(['is_404' => false]);
        $page->update(['is_404' => true]);
        return $page->fresh();
    }

    public function addSection(Page $page, array $sectionData): Page
    {
        $page->sections()->create($sectionData);
        return $page->fresh(['sections']);
    }

    public function updateSection(Page $page, string $sectionId, array $data): Page
    {
        $page->sections()->where('id', $sectionId)->update($data);
        return $page->fresh(['sections']);
    }

    public function deleteSection(Page $page, string $sectionId): Page
    {
        $page->sections()->where('id', $sectionId)->delete();
        return $page->fresh(['sections']);
    }

    public function reorderSections(Page $page, array $order): Page
    {
        foreach ($order as $index => $id) {
            $page->sections()->where('id', $id)->update(['sort_order' => $index]);
        }
        return $page->fresh(['sections']);
    }

    public function changeTemplate(Page $page, string $template): Page
    {
        $page->update(['template' => $template]);
        return $page->fresh();
    }

    public function lock(Page $page): Page
    {
        $page->update(['locked_by' => auth()->id(), 'locked_at' => now()]);
        return $page->fresh();
    }

    public function unlock(Page $page): Page
    {
        $page->update(['locked_by' => null, 'locked_at' => null]);
        return $page->fresh();
    }

    public function duplicate(Page $page, ?string $newSlug = null): Page
    {
        return DB::transaction(function () use ($page, $newSlug) {
            $clone = $page->replicate(['id', 'status', 'published_at', 'is_homepage']);
            $clone->status = 'draft';
            $clone->is_homepage = false;
            $clone->save();

            foreach ($page->translations as $trans) {
                $newTrans = $trans->replicate(['id', 'page_id']);
                $newTrans->page_id = $clone->id;
                $newTrans->slug = $newSlug ?? $trans->slug . '-copy';
                $newTrans->save();
            }

            return $clone->fresh(['translations']);
        });
    }

    public function preview(Page $page): array
    {
        return [
            'id' => $page->id,
            'template' => $page->template,
            'content' => $page->translation?->content,
            'sections' => $page->sections,
            'url' => '/preview/page/' . $page->id,
        ];
    }

    public function getRevisions(Page $page): Collection
    {
        return $page->revisions()->get();
    }

    public function restoreRevision(Page $page, int $revisionNumber): Page
    {
        $page->restoreRevision($revisionNumber);
        return $page->fresh();
    }
}
