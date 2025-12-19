# 📋 المرحلة 2: نظام التخزين المؤقت (Caching)

## الهدف
تحسين أداء التطبيق عبر تخزين البيانات المتكررة وتقليل استعلامات قاعدة البيانات.

---

## المهام

### 2.1 إنشاء HasCaching Trait

**الملف:** `modules/Core/Traits/HasCaching.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

trait HasCaching
{
    protected static int $cacheTtl = 3600;

    protected static function getCachePrefix(): string
    {
        return strtolower(class_basename(static::class));
    }

    protected static function getCacheKey(string $suffix): string
    {
        return static::getCachePrefix() . ':' . $suffix;
    }

    public static function cached(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember(
            static::getCacheKey($key),
            $ttl ?? static::$cacheTtl,
            $callback
        );
    }

    public static function forgetCache(string $key): bool
    {
        return Cache::forget(static::getCacheKey($key));
    }

    protected static function bootHasCaching(): void
    {
        static::created(fn (Model $model) => static::clearRelatedCache($model));
        static::updated(fn (Model $model) => static::clearRelatedCache($model));
        static::deleted(fn (Model $model) => static::clearRelatedCache($model));
    }

    protected static function clearRelatedCache(Model $model): void
    {
        static::forgetCache('all');
        static::forgetCache('count');
        static::forgetCache("id:{$model->id}");
    }
}
```

---

### 2.2 إنشاء CacheService

**الملف:** `modules/Core/Services/CacheService.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

final class CacheService
{
    public function remember(string $key, Closure $callback, int $ttl = 3600): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    public function has(string $key): bool
    {
        return Cache::has($key);
    }

    public function flush(): bool
    {
        return Cache::flush();
    }

    public function generateKey(string $model, array $params = []): string
    {
        $hash = empty($params) ? '' : ':' . md5(serialize($params));
        return "query:{$model}{$hash}";
    }
}
```

---

### 2.3 تطبيق Caching على ArticleQueryService

**تحديث:** `modules/Content/Application/Services/ArticleQueryService.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Content\Contracts\ArticleRepositoryInterface;

final class ArticleQueryService
{
    private const CACHE_PREFIX = 'articles';
    private const CACHE_TTL = 3600;

    public function __construct(
        private readonly ArticleRepositoryInterface $repository
    ) {}

    public function getPublished(int $limit = 10)
    {
        return Cache::remember(
            self::CACHE_PREFIX . ":published:{$limit}",
            self::CACHE_TTL,
            fn () => $this->repository->getPublished($limit)
        );
    }

    public function getFeatured(int $limit = 5)
    {
        return Cache::remember(
            self::CACHE_PREFIX . ":featured:{$limit}",
            self::CACHE_TTL,
            fn () => $this->repository->getFeatured($limit)
        );
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . ':published:10');
        Cache::forget(self::CACHE_PREFIX . ':featured:5');
    }
}
```

---

### 2.4 إنشاء Artisan Command

**الملف:** `app/Console/Commands/CmsCacheClearCommand.php`

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CmsCacheClearCommand extends Command
{
    protected $signature = 'cms:cache:clear {--module= : Module name}';
    protected $description = 'Clear CMS cache';

    public function handle(): int
    {
        $module = $this->option('module');

        if ($module) {
            $this->info("Clearing cache for module: {$module}");
            // Clear specific module cache
        } else {
            Cache::flush();
            $this->info('All cache cleared.');
        }

        return self::SUCCESS;
    }
}
```

---

## ✅ قائمة التحقق

- [ ] إنشاء `HasCaching` Trait
- [ ] إنشاء `CacheService`
- [ ] تطبيق Caching على `ArticleQueryService`
- [ ] تطبيق Caching على `PageQueryService`
- [ ] تطبيق Caching على `LanguageService`
- [ ] إنشاء `cms:cache:clear` Command
- [ ] إضافة Cache invalidation عند التعديل
- [ ] اختبار الأداء

---

## 📊 مقاييس النجاح

| المقياس | قبل | بعد (الهدف) |
|---------|-----|-------------|
| استعلامات/طلب | ~20 | ~5 |
| زمن الاستجابة | ~200ms | ~50ms |
| Cache Hit Rate | 0% | 80%+ |

---

## 🧪 اختبار المرحلة

```bash
# اختبار الـ Cache
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');

# مسح الـ Cache
php artisan cms:cache:clear
```
