# 📋 المرحلة 5: Query Objects وتحسين الأداء

## الهدف
تحسين الاستعلامات المعقدة عبر Query Objects منفصلة وحل مشاكل N+1.

---

## المهام

### 5.1 إنشاء Base Query Class

**الملف:** `modules/Core/Queries/Query.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class Query
{
    protected Builder $builder;
    protected array $filters = [];
    protected array $with = [];
    protected ?string $orderBy = null;
    protected string $orderDirection = 'desc';

    abstract protected function baseQuery(): Builder;

    public function __construct()
    {
        $this->builder = $this->baseQuery();
    }

    public function filter(array $filters): self
    {
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    public function with(array $relations): self
    {
        $this->with = array_merge($this->with, $relations);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'desc'): self
    {
        $this->orderBy = $column;
        $this->orderDirection = $direction;
        return $this;
    }

    public function get(): Collection
    {
        return $this->prepareQuery()->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->prepareQuery()->paginate($perPage);
    }

    public function first(): mixed
    {
        return $this->prepareQuery()->first();
    }

    public function count(): int
    {
        return $this->prepareQuery()->count();
    }

    protected function prepareQuery(): Builder
    {
        $this->applyFilters();
        $this->applyRelations();
        $this->applyOrder();
        return $this->builder;
    }

    protected function applyFilters(): void
    {
        foreach ($this->filters as $key => $value) {
            if ($value === null) continue;
            
            $method = 'filter' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    protected function applyRelations(): void
    {
        if (!empty($this->with)) {
            $this->builder->with($this->with);
        }
    }

    protected function applyOrder(): void
    {
        if ($this->orderBy) {
            $this->builder->orderBy($this->orderBy, $this->orderDirection);
        }
    }
}
```

---

### 5.2 إنشاء PublishedArticlesQuery

**الملف:** `modules/Content/Queries/PublishedArticlesQuery.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Queries;

use Illuminate\Database\Eloquent\Builder;
use Modules\Content\Domain\Models\Article;
use Modules\Core\Queries\Query;

final class PublishedArticlesQuery extends Query
{
    protected function baseQuery(): Builder
    {
        return Article::query()
            ->published()
            ->with(['author', 'featuredImage', 'translations']);
    }

    protected function filterType(string $type): void
    {
        $this->builder->where('type', $type);
    }

    protected function filterAuthor(string $authorId): void
    {
        $this->builder->where('author_id', $authorId);
    }

    protected function filterFeatured(bool $featured): void
    {
        if ($featured) {
            $this->builder->where('is_featured', true);
        }
    }

    protected function filterSearch(string $term): void
    {
        $this->builder->whereHas('translations', function ($q) use ($term) {
            $q->where('title', 'LIKE', "%{$term}%")
              ->orWhere('content', 'LIKE', "%{$term}%");
        });
    }

    protected function filterCategory(string $categoryId): void
    {
        $this->builder->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }
}
```

---

### 5.3 استخدام Query Objects

**تحديث:** `modules/Content/Application/Services/ArticleQueryService.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Modules\Content\Queries\PublishedArticlesQuery;

final class ArticleQueryService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        return (new PublishedArticlesQuery())
            ->filter($filters)
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    public function getFeatured(int $limit = 5)
    {
        return (new PublishedArticlesQuery())
            ->filter(['featured' => true])
            ->orderBy('published_at', 'desc')
            ->get()
            ->take($limit);
    }

    public function search(string $term, int $perPage = 15)
    {
        return (new PublishedArticlesQuery())
            ->filter(['search' => $term])
            ->paginate($perPage);
    }
}
```

---

### 5.4 إنشاء Query Monitor

**الملف:** `modules/Core/Services/QueryMonitor.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class QueryMonitor
{
    private static array $queries = [];
    private static float $slowThreshold = 1000; // ms

    public static function enable(): void
    {
        DB::listen(function ($query) {
            self::$queries[] = [
                'sql' => $query->sql,
                'time' => $query->time,
            ];

            if ($query->time >= self::$slowThreshold) {
                Log::warning('Slow Query', [
                    'sql' => $query->sql,
                    'time_ms' => $query->time,
                ]);
            }
        });
    }

    public static function getQueries(): array
    {
        return self::$queries;
    }

    public static function getCount(): int
    {
        return count(self::$queries);
    }

    public static function getTotalTime(): float
    {
        return array_sum(array_column(self::$queries, 'time'));
    }

    public static function reset(): void
    {
        self::$queries = [];
    }
}
```

---

## ✅ قائمة التحقق

- [ ] إنشاء `Query` Base Class
- [ ] إنشاء `PublishedArticlesQuery`
- [ ] إنشاء `DraftArticlesQuery`
- [ ] إنشاء `PublishedPagesQuery`
- [ ] تحديث Services لاستخدام Query Objects
- [ ] إنشاء `QueryMonitor`
- [ ] إضافة Database Indexes
- [ ] حل مشاكل N+1
- [ ] اختبار الأداء

---

## 📊 Database Indexes المقترحة

```sql
-- articles table
CREATE INDEX idx_articles_status ON articles(status);
CREATE INDEX idx_articles_type ON articles(type);
CREATE INDEX idx_articles_published_at ON articles(published_at);
CREATE INDEX idx_articles_author ON articles(author_id);
CREATE INDEX idx_articles_featured ON articles(is_featured);

-- article_translations table
CREATE INDEX idx_article_trans_locale ON article_translations(locale);
CREATE INDEX idx_article_trans_slug ON article_translations(slug);
```

---

## 🧪 اختبار المرحلة

```bash
# تمكين Query Monitoring
php artisan tinker
>>> \Modules\Core\Services\QueryMonitor::enable();
>>> \Modules\Content\Application\Services\ArticleQueryService::list();
>>> \Modules\Core\Services\QueryMonitor::getCount();
>>> \Modules\Core\Services\QueryMonitor::getTotalTime();
```
