# 📋 المرحلة 1: تحسين Repository Pattern

## الهدف
تطبيق Repository Pattern بشكل كامل مع Interfaces لتحقيق:
- **Dependency Inversion Principle**
- سهولة الاختبار (Mocking)
- إمكانية تبديل التطبيقات

---

## المهام

### 1.1 إنشاء RepositoryInterface

**الملف:** `modules/Core/Contracts/RepositoryInterface.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface
{
    public function all(array $columns = ['*']): Collection;
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;
    public function find(string $id, array $columns = ['*']): ?Model;
    public function findOrFail(string $id, array $columns = ['*']): Model;
    public function findWhere(array $criteria, array $columns = ['*']): Collection;
    public function findFirstWhere(array $criteria, array $columns = ['*']): ?Model;
    public function create(array $data): Model;
    public function update(string $id, array $data): Model;
    public function delete(string $id): bool;
    public function with(array $relations): self;
    public function orderBy(string $column, string $direction = 'asc'): self;
}
```

---

### 1.2 إنشاء BaseRepository

**الملف:** `modules/Core/Repositories/BaseRepository.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Contracts\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;
    protected Builder $query;

    public function __construct()
    {
        $this->model = $this->resolveModel();
        $this->resetQuery();
    }

    abstract protected function resolveModel(): Model;

    protected function resetQuery(): void
    {
        $this->query = $this->model->newQuery();
    }

    public function all(array $columns = ['*']): Collection
    {
        $result = $this->query->get($columns);
        $this->resetQuery();
        return $result;
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        $result = $this->query->paginate($perPage, $columns);
        $this->resetQuery();
        return $result;
    }

    public function find(string $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    public function findOrFail(string $id, array $columns = ['*']): Model
    {
        return $this->model->findOrFail($id, $columns);
    }

    public function findWhere(array $criteria, array $columns = ['*']): Collection
    {
        return $this->model->where($criteria)->get($columns);
    }

    public function findFirstWhere(array $criteria, array $columns = ['*']): ?Model
    {
        return $this->model->where($criteria)->first($columns);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }

    public function delete(string $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function with(array $relations): self
    {
        $this->query = $this->query->with($relations);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->query = $this->query->orderBy($column, $direction);
        return $this;
    }
}
```

---

### 1.3 إنشاء ArticleRepositoryInterface

**الملف:** `modules/Content/Contracts/ArticleRepositoryInterface.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Content\Domain\Models\Article;
use Modules\Core\Contracts\RepositoryInterface;

interface ArticleRepositoryInterface extends RepositoryInterface
{
    public function getPublished(int $limit = 10): Collection;
    public function getFeatured(int $limit = 5): Collection;
    public function getByAuthor(string $authorId): Collection;
    public function getByType(string $type): Collection;
    public function findBySlug(string $slug, ?string $locale = null): ?Article;
    public function getRelated(Article $article, int $limit = 5): Collection;
    public function incrementViews(string $id): void;
}
```

---

### 1.4 تحديث Service Provider

**الملف:** `modules/Content/Providers/ContentServiceProvider.php`

```php
// إضافة في دالة register()
$this->app->bind(
    \Modules\Content\Contracts\ArticleRepositoryInterface::class,
    \Modules\Content\Domain\Repositories\ArticleRepository::class
);
```

---

## ✅ قائمة التحقق

- [ ] إنشاء `RepositoryInterface` في Core
- [ ] إنشاء `BaseRepository` في Core
- [ ] إنشاء `ArticleRepositoryInterface`
- [ ] إنشاء `PageRepositoryInterface`
- [ ] إنشاء `UserRepositoryInterface`
- [ ] تحديث `ArticleRepository` لتطبيق Interface
- [ ] تحديث `PageRepository` لتطبيق Interface
- [ ] تسجيل Bindings في Service Providers
- [ ] تحديث Services لاستخدام Interfaces
- [ ] اختبار جميع الوظائف

---

## 📁 الملفات المطلوب إنشاؤها

```
modules/
├── Core/
│   ├── Contracts/
│   │   └── RepositoryInterface.php      ✨ جديد
│   └── Repositories/
│       └── BaseRepository.php           ✨ جديد
├── Content/
│   └── Contracts/
│       ├── ArticleRepositoryInterface.php  ✨ جديد
│       └── PageRepositoryInterface.php     ✨ جديد
└── Users/
    └── Contracts/
        └── UserRepositoryInterface.php     ✨ جديد
```

---

## 🧪 اختبار المرحلة

```bash
# تشغيل الاختبارات
php artisan test --filter=Repository

# التأكد من عدم وجود أخطاء
php artisan tinker
>>> app(\Modules\Content\Contracts\ArticleRepositoryInterface::class)->all();
```
