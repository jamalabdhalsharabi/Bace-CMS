# 📋 المرحلة 3: استراتيجية الاختبارات (Testing)

## الهدف
بناء نظام اختبارات شامل يغطي Unit Tests, Feature Tests, و Integration Tests.

---

## المهام

### 3.1 تحديث TestCase الأساسية

**الملف:** `tests/TestCase.php`

```php
<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Users\Domain\Models\User;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function actingAsUser(array $attributes = []): self
    {
        $user = User::factory()->create($attributes);
        return $this->actingAs($user);
    }

    protected function actingAsAdmin(): self
    {
        $admin = User::factory()->create(['status' => 'active']);
        return $this->actingAs($admin, 'sanctum');
    }

    protected function assertApiSuccess($response): void
    {
        $response->assertJson(['success' => true]);
    }

    protected function assertApiError($response, int $status = 400): void
    {
        $response->assertStatus($status)
                 ->assertJson(['success' => false]);
    }
}
```

---

### 3.2 إنشاء ArticleFactory

**الملف:** `modules/Content/Database/Factories/ArticleFactory.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Content\Domain\Models\Article;
use Modules\Users\Domain\Models\User;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'type' => $this->faker->randomElement(['post', 'news']),
            'status' => 'draft',
            'is_featured' => false,
            'allow_comments' => true,
            'view_count' => 0,
            'reading_time' => $this->faker->numberBetween(1, 20),
            'created_by' => fn (array $attrs) => $attrs['author_id'],
        ];
    }

    public function published(): self
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function draft(): self
    {
        return $this->state(fn () => [
            'status' => 'draft',
        ]);
    }

    public function featured(): self
    {
        return $this->state(fn () => [
            'is_featured' => true,
        ]);
    }
}
```

---

### 3.3 Unit Test للـ Actions

**الملف:** `modules/Content/Tests/Unit/CreateArticleActionTest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Tests\Unit;

use Modules\Content\Application\Actions\Article\CreateArticleAction;
use Modules\Content\Domain\DTO\ArticleData;
use Modules\Content\Domain\Models\Article;
use Modules\Users\Domain\Models\User;
use Tests\TestCase;

class CreateArticleActionTest extends TestCase
{
    /** @test */
    public function it_creates_article_with_translations(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $action = app(CreateArticleAction::class);
        
        $data = new ArticleData(
            author_id: $user->id,
            type: 'post',
            status: 'draft',
            translations: [
                'en' => ['title' => 'Test', 'content' => 'Content'],
            ],
        );

        $article = $action->execute($data);

        $this->assertInstanceOf(Article::class, $article);
        $this->assertEquals('draft', $article->status);
    }

    /** @test */
    public function it_fires_article_created_event(): void
    {
        \Illuminate\Support\Facades\Event::fake();
        
        $user = User::factory()->create();
        $this->actingAs($user);

        $action = app(CreateArticleAction::class);
        $data = new ArticleData(
            author_id: $user->id,
            translations: [
                'en' => ['title' => 'Test', 'content' => 'Content'],
            ],
        );

        $action->execute($data);

        \Illuminate\Support\Facades\Event::assertDispatched(
            \Modules\Content\Domain\Events\ArticleCreated::class
        );
    }
}
```

---

### 3.4 Feature Test للـ API

**الملف:** `modules/Content/Tests/Feature/ArticleApiTest.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Content\Tests\Feature;

use Modules\Content\Domain\Models\Article;
use Modules\Users\Domain\Models\User;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['status' => 'active']);
    }

    /** @test */
    public function guest_can_list_published_articles(): void
    {
        Article::factory()->count(3)->published()->create();

        $response = $this->getJson('/api/v2/articles');

        $response->assertOk()
                 ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function authenticated_user_can_create_article(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v2/articles', [
                'type' => 'post',
                'translations' => [
                    'en' => ['title' => 'New Article', 'content' => 'Content'],
                ],
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('articles', ['type' => 'post']);
    }

    /** @test */
    public function user_can_update_own_article(): void
    {
        $article = Article::factory()
            ->create(['author_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v2/articles/{$article->id}", [
                'translations' => [
                    'en' => ['title' => 'Updated', 'content' => 'New content'],
                ],
            ]);

        $response->assertOk();
    }

    /** @test */
    public function user_can_delete_own_article(): void
    {
        $article = Article::factory()
            ->create(['author_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v2/articles/{$article->id}");

        $response->assertOk();
        $this->assertSoftDeleted('articles', ['id' => $article->id]);
    }
}
```

---

## ✅ قائمة التحقق

- [ ] تحديث `TestCase` الأساسية
- [ ] إنشاء `ArticleFactory`
- [ ] إنشاء `PageFactory`
- [ ] إنشاء `UserFactory`
- [ ] كتابة Unit Tests لـ Actions
- [ ] كتابة Feature Tests لـ Article API
- [ ] كتابة Feature Tests لـ Page API
- [ ] كتابة Feature Tests لـ Auth API
- [ ] إعداد GitHub Actions للـ CI
- [ ] تحقيق تغطية 80%+

---

## 📊 هيكل الاختبارات

```
tests/
├── TestCase.php
├── Feature/
│   └── ExampleTest.php
└── Unit/
    └── ExampleTest.php

modules/Content/Tests/
├── Unit/
│   ├── CreateArticleActionTest.php
│   └── PublishArticleActionTest.php
└── Feature/
    └── ArticleApiTest.php
```

---

## 🧪 تشغيل الاختبارات

```bash
# جميع الاختبارات
php artisan test

# اختبارات موديول معين
php artisan test modules/Content/Tests

# مع التغطية
php artisan test --coverage

# اختبار محدد
php artisan test --filter=ArticleApiTest
```
