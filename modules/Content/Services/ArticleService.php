<?php

declare(strict_types=1);

namespace Modules\Content\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Content\Contracts\ArticleServiceContract;
use Modules\Content\Domain\Models\Article;

class ArticleService implements ArticleServiceContract
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Article::with(['author', 'featuredImage', 'translation']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('translations', fn ($q) => 
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%")
            );
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(string $id): ?Article
    {
        return Article::with(['author', 'featuredImage', 'translations'])->find($id);
    }

    public function findBySlug(string $slug, ?string $locale = null): ?Article
    {
        return Article::findBySlug($slug, $locale)?->load(['author', 'featuredImage', 'translations']);
    }

    public function create(array $data): Article
    {
        return DB::transaction(function () use ($data) {
            $article = Article::create([
                'author_id' => $data['author_id'] ?? auth()->id(),
                'featured_image_id' => $data['featured_image_id'] ?? null,
                'type' => $data['type'] ?? 'post',
                'status' => $data['status'] ?? 'draft',
                'is_featured' => $data['is_featured'] ?? false,
                'is_commentable' => $data['is_commentable'] ?? true,
                'view_count' => 0,
                'reading_time' => 0,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $article->translations()->create([
                        'locale' => $locale,
                        'title' => $trans['title'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                        'excerpt' => $trans['excerpt'] ?? null,
                        'content' => $trans['content'] ?? null,
                        'meta_title' => $trans['meta_title'] ?? null,
                        'meta_description' => $trans['meta_description'] ?? null,
                        'meta_keywords' => $trans['meta_keywords'] ?? null,
                    ]);
                }
            }

            $article->reading_time = $article->calculateReadingTime();
            $article->save();

            return $article->fresh(['author', 'featuredImage', 'translations']);
        });
    }

    public function update(Article $article, array $data): Article
    {
        return DB::transaction(function () use ($article, $data) {
            $article->update([
                'featured_image_id' => $data['featured_image_id'] ?? $article->featured_image_id,
                'type' => $data['type'] ?? $article->type,
                'is_featured' => $data['is_featured'] ?? $article->is_featured,
                'is_commentable' => $data['is_commentable'] ?? $article->is_commentable,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $article->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'title' => $trans['title'],
                            'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                            'excerpt' => $trans['excerpt'] ?? null,
                            'content' => $trans['content'] ?? null,
                            'meta_title' => $trans['meta_title'] ?? null,
                            'meta_description' => $trans['meta_description'] ?? null,
                            'meta_keywords' => $trans['meta_keywords'] ?? null,
                        ]
                    );
                }
            }

            $article->reading_time = $article->calculateReadingTime();
            $article->save();

            return $article->fresh(['author', 'featuredImage', 'translations']);
        });
    }

    public function publish(Article $article): Article
    {
        $article->update([
            'status' => 'published',
            'published_at' => $article->published_at ?? now(),
        ]);

        return $article->fresh();
    }

    public function unpublish(Article $article): Article
    {
        $article->update(['status' => 'draft']);

        return $article->fresh();
    }

    public function schedule(Article $article, Carbon $publishAt): Article
    {
        $article->update([
            'status' => 'published',
            'published_at' => $publishAt,
        ]);

        return $article->fresh();
    }

    public function archive(Article $article): Article
    {
        $article->update(['status' => 'archived']);

        return $article->fresh();
    }

    public function delete(Article $article): bool
    {
        return $article->delete();
    }

    public function duplicate(Article $article): Article
    {
        return DB::transaction(function () use ($article) {
            $newArticle = $article->replicate(['view_count', 'published_at']);
            $newArticle->status = 'draft';
            $newArticle->save();

            foreach ($article->translations as $translation) {
                $newArticle->translations()->create([
                    'locale' => $translation->locale,
                    'title' => $translation->title . ' (Copy)',
                    'slug' => $translation->slug . '-copy-' . time(),
                    'excerpt' => $translation->excerpt,
                    'content' => $translation->content,
                    'meta_title' => $translation->meta_title,
                    'meta_description' => $translation->meta_description,
                    'meta_keywords' => $translation->meta_keywords,
                ]);
            }

            return $newArticle->fresh(['translations']);
        });
    }

    public function forceDelete(Article $article): bool
    {
        return $article->forceDelete();
    }

    public function restore(string $id): ?Article
    {
        $article = Article::withTrashed()->find($id);
        $article?->restore();
        return $article;
    }

    public function saveDraft(Article $article, array $data): Article
    {
        $data['status'] = 'draft';
        return $this->update($article, $data);
    }

    public function autoSave(Article $article, array $data): Article
    {
        return $this->update($article, $data);
    }

    public function submitForReview(Article $article): Article
    {
        $article->update(['status' => 'pending_review', 'submitted_at' => now()]);
        return $article->fresh();
    }

    public function startReview(Article $article): Article
    {
        $article->update(['status' => 'in_review', 'reviewed_by' => auth()->id()]);
        return $article->fresh();
    }

    public function approve(Article $article, ?string $notes = null): Article
    {
        $article->update(['status' => 'approved', 'review_notes' => $notes]);
        return $article->fresh();
    }

    public function reject(Article $article, ?string $notes = null): Article
    {
        $article->update(['status' => 'rejected', 'review_notes' => $notes]);
        return $article->fresh();
    }

    public function republish(Article $article): Article
    {
        $article->update(['status' => 'published', 'published_at' => now()]);
        return $article->fresh();
    }

    public function cancelSchedule(Article $article): Article
    {
        $article->update(['status' => 'draft', 'published_at' => null]);
        return $article->fresh();
    }

    public function unarchive(Article $article): Article
    {
        $article->update(['status' => 'draft', 'archived_at' => null]);
        return $article->fresh();
    }

    public function pin(Article $article): Article
    {
        $article->update(['is_pinned' => true]);
        return $article->fresh();
    }

    public function unpin(Article $article): Article
    {
        $article->update(['is_pinned' => false]);
        return $article->fresh();
    }

    public function convertType(Article $article, string $newType): Article
    {
        $article->update(['type' => $newType]);
        return $article->fresh();
    }

    public function syncCategories(Article $article, array $categoryIds): Article
    {
        $article->categories()->sync($categoryIds);
        return $article->fresh(['categories']);
    }

    public function addTags(Article $article, array $tags): Article
    {
        foreach ($tags as $tag) {
            $tagModel = \Modules\Taxonomy\Domain\Models\Term::firstOrCreate(
                ['slug' => Str::slug($tag), 'taxonomy_id' => $this->getTagTaxonomyId()],
                ['name' => $tag]
            );
            $article->tags()->syncWithoutDetaching($tagModel->id);
        }
        return $article->fresh(['tags']);
    }

    public function removeTags(Article $article, array $tagIds): Article
    {
        $article->tags()->detach($tagIds);
        return $article->fresh(['tags']);
    }

    public function attachRelated(Article $article, array $articleIds): Article
    {
        $article->relatedArticles()->sync($articleIds);
        return $article->fresh(['relatedArticles']);
    }

    public function setFeaturedImage(Article $article, string $mediaId): Article
    {
        $article->update(['featured_image_id' => $mediaId]);
        return $article->fresh(['featuredImage']);
    }

    public function enableComments(Article $article): Article
    {
        $article->update(['is_commentable' => true, 'comments_closed' => false]);
        return $article->fresh();
    }

    public function disableComments(Article $article): Article
    {
        $article->update(['is_commentable' => false]);
        return $article->fresh();
    }

    public function closeComments(Article $article): Article
    {
        $article->update(['comments_closed' => true]);
        return $article->fresh();
    }

    public function getRevisions(Article $article): \Illuminate\Database\Eloquent\Collection
    {
        return $article->revisions()->get();
    }

    public function restoreRevision(Article $article, int $revisionNumber): Article
    {
        $article->restoreRevision($revisionNumber);
        return $article->fresh();
    }

    public function shareOnSocial(Article $article, array $platforms): array
    {
        $results = [];
        foreach ($platforms as $platform) {
            $results[$platform] = ['status' => 'queued'];
        }
        return $results;
    }

    public function sendToNewsletter(Article $article): bool
    {
        // Queue newsletter sending
        return true;
    }

    public function getAnalytics(Article $article): array
    {
        return [
            'view_count' => $article->view_count,
            'reading_time' => $article->reading_time,
            'comments_count' => $article->comments()->count(),
            'shares' => 0,
        ];
    }

    protected function getTagTaxonomyId(): string
    {
        return \Modules\Taxonomy\Domain\Models\Taxonomy::where('slug', 'tags')->value('id') ?? '';
    }
}
