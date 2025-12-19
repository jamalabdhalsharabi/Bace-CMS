# 📋 المرحلة 6: Event Sourcing و Audit Trail

## الهدف
إنشاء نظام شامل لتتبع التغييرات والأحداث لضمان الشفافية والأمان.

---

## المهام

### 6.1 إنشاء AuditLog Model

**الملف:** `modules/Core/Domain/Models/AuditLog.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasUuids;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'tags' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForModel($query, string $type, string $id)
    {
        return $query->where('auditable_type', $type)
                     ->where('auditable_id', $id);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfEvent($query, string $event)
    {
        return $query->where('event', $event);
    }
}
```

---

### 6.2 إنشاء Auditable Trait

**الملف:** `modules/Core/Traits/Auditable.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Domain\Models\AuditLog;

trait Auditable
{
    protected static array $auditExclude = ['updated_at', 'remember_token'];

    protected static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            static::audit($model, 'created', [], $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $old = $model->getOriginal();
            $new = $model->getChanges();
            
            // Remove excluded fields
            $old = array_diff_key($old, array_flip(static::$auditExclude));
            $new = array_diff_key($new, array_flip(static::$auditExclude));
            
            if (!empty($new)) {
                static::audit($model, 'updated', $old, $new);
            }
        });

        static::deleted(function (Model $model) {
            static::audit($model, 'deleted', $model->getAttributes(), []);
        });
    }

    protected static function audit(Model $model, string $event, array $old, array $new): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'event' => $event,
            'old_values' => $old,
            'new_values' => $new,
            'url' => request()->fullUrl(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function audits()
    {
        return $this->morphMany(AuditLog::class, 'auditable')
                    ->orderBy('created_at', 'desc');
    }

    public function getAuditHistory(int $limit = 50)
    {
        return $this->audits()->limit($limit)->get();
    }
}
```

---

### 6.3 إنشاء Domain Events

**الملف:** `modules/Content/Domain/Events/ArticleEvent.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Content\Domain\Models\Article;

abstract class ArticleEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Article $article,
        public readonly ?string $userId = null,
        public readonly array $metadata = []
    ) {
        $this->userId = $userId ?? auth()->id();
    }
}
```

**الملف:** `modules/Content/Domain/Events/ArticlePublished.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Events;

class ArticlePublished extends ArticleEvent
{
    //
}
```

**الملف:** `modules/Content/Domain/Events/ArticleStatusChanged.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Events;

class ArticleStatusChanged extends ArticleEvent
{
    public function __construct(
        Article $article,
        public readonly string $oldStatus,
        public readonly string $newStatus,
        ?string $userId = null
    ) {
        parent::__construct($article, $userId);
    }
}
```

---

### 6.4 إنشاء Event Listeners

**الملف:** `modules/Content/Domain/Listeners/LogArticleActivity.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Listeners;

use Modules\Content\Domain\Events\ArticleEvent;
use Modules\Core\Domain\Models\AuditLog;

class LogArticleActivity
{
    public function handle(ArticleEvent $event): void
    {
        AuditLog::create([
            'user_id' => $event->userId,
            'auditable_type' => get_class($event->article),
            'auditable_id' => $event->article->id,
            'event' => class_basename($event),
            'old_values' => [],
            'new_values' => $event->metadata,
            'ip_address' => request()->ip(),
        ]);
    }
}
```

---

### 6.5 Migration للـ AuditLog

**الملف:** `modules/Core/Database/Migrations/create_audit_logs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('auditable_type');
            $table->uuid('auditable_id');
            $table->string('event', 50);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('url', 2048)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('event');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
```

---

### 6.6 AuditLog Service

**الملف:** `modules/Core/Services/AuditService.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Core\Domain\Models\AuditLog;

final class AuditService
{
    public function log(Model $model, string $event, array $data = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'event' => $event,
            'new_values' => $data,
            'ip_address' => request()->ip(),
        ]);
    }

    public function getHistory(Model $model, int $limit = 50): Collection
    {
        return AuditLog::forModel(get_class($model), $model->getKey())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getUserActivity(string $userId, int $limit = 100): Collection
    {
        return AuditLog::byUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRecentActivity(int $limit = 100): Collection
    {
        return AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
```

---

## ✅ قائمة التحقق

- [ ] إنشاء `AuditLog` Model
- [ ] إنشاء Migration للـ audit_logs
- [ ] إنشاء `Auditable` Trait
- [ ] تطبيق Trait على Article Model
- [ ] تطبيق Trait على Page Model
- [ ] تطبيق Trait على User Model
- [ ] إنشاء Domain Events
- [ ] إنشاء Event Listeners
- [ ] إنشاء `AuditService`
- [ ] إنشاء Audit API endpoints
- [ ] اختبار النظام

---

## 📊 الأحداث المتتبعة

| الحدث | الوصف |
|-------|-------|
| `created` | إنشاء سجل جديد |
| `updated` | تحديث سجل |
| `deleted` | حذف سجل |
| `restored` | استعادة سجل محذوف |
| `published` | نشر محتوى |
| `unpublished` | إلغاء نشر محتوى |
| `status_changed` | تغيير الحالة |
| `login` | تسجيل دخول |
| `logout` | تسجيل خروج |

---

## 🧪 اختبار المرحلة

```bash
# إنشاء مقال واختبار الـ Audit
php artisan tinker
>>> $article = \Modules\Content\Domain\Models\Article::first();
>>> $article->update(['status' => 'published']);
>>> $article->audits()->get();

# عرض سجل النشاط
>>> app(\Modules\Core\Services\AuditService::class)->getRecentActivity(10);
```
