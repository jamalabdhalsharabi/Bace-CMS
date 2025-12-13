# Clean Architecture - CMS System

## نظرة عامة

تم إعادة هيكلة النظام بالكامل ليعمل وفق مبادئ Clean Architecture و Domain-Driven Design (DDD).
يوفر الهيكل الجديد:
- **قابلية التوسع**: سهولة إضافة ميزات جديدة
- **قابلية الصيانة**: كود نظيف ومنظم
- **قابلية الاختبار**: كل مكون قابل للاختبار بشكل مستقل
- **التوافق العكسي**: الكود القديم يعمل بجانب الجديد

## الهيكل الجديد للمشروع

```
Modules/
├── Core/                                    # Base classes and shared components
│   ├── Application/
│   │   └── Actions/
│   │       └── Action.php                   # Base Action class
│   ├── Traits/
│   │   ├── HasPublishingWorkflow.php        # Publishing workflow trait
│   │   ├── HasFeatured.php                  # Featured functionality trait
│   │   └── HasSlug.php                      # Slug generation trait
│   └── Domain/
│       ├── Contracts/
│       │   └── RepositoryInterface.php      # Repository contract
│       ├── DTO/
│       │   └── DataTransferObject.php       # Base DTO class
│       ├── Repositories/
│       │   └── BaseRepository.php           # Base Repository implementation
│       ├── ValueObjects/
│       │   ├── Uuid.php                     # UUID value object
│       │   └── Status.php                   # Status value object
│       └── States/
│           └── State.php                    # Base State Machine class
│
├── Content/                                 # Content Module (Articles, Pages)
│   ├── Application/
│   │   ├── Actions/
│   │   │   └── Article/
│   │   │       ├── CreateArticleAction.php
│   │   │       ├── UpdateArticleAction.php
│   │   │       ├── PublishArticleAction.php
│   │   │       ├── DeleteArticleAction.php
│   │   │       └── DuplicateArticleAction.php
│   │   └── Services/
│   │       ├── ArticleQueryService.php      # Read operations
│   │       ├── ArticleCommandService.php    # Write operations
│   │       ├── ArticleWorkflowService.php   # State transitions
│   │       ├── ArticleTaxonomyService.php   # Categories & Tags
│   │       ├── ArticleMediaService.php      # Media attachments
│   │       └── ArticleCommentService.php    # Comment settings
│   ├── Domain/
│   │   ├── DTO/
│   │   │   ├── ArticleData.php
│   │   │   └── TranslationData.php
│   │   ├── Events/
│   │   │   ├── ArticleCreated.php
│   │   │   ├── ArticlePublished.php
│   │   │   └── ArticleStatusChanged.php
│   │   ├── Models/
│   │   │   ├── Article.php
│   │   │   ├── ArticleTranslation.php
│   │   │   └── Page.php
│   │   ├── Repositories/
│   │   │   └── ArticleRepository.php
│   │   └── States/
│   │       ├── ArticleState.php
│   │       ├── DraftState.php
│   │       ├── PendingReviewState.php
│   │       ├── InReviewState.php
│   │       ├── ApprovedState.php
│   │       ├── RejectedState.php
│   │       ├── PublishedState.php
│   │       ├── ScheduledState.php
│   │       └── ArchivedState.php
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── ArticleController.php        # Legacy controller
│   │   │   └── ArticleControllerV2.php      # New clean controller
│   │   ├── Requests/
│   │   │   ├── CreateArticleRequest.php
│   │   │   └── UpdateArticleRequest.php
│   │   └── Resources/
│   │       └── ArticleResource.php
│   └── Providers/
│       └── ContentServiceProvider.php
│
├── Products/                                # Products Module
│   ├── Application/
│   │   ├── Actions/
│   │   │   └── Product/
│   │   │       └── CreateProductAction.php
│   │   └── Services/
│   │       ├── ProductQueryService.php
│   │       ├── ProductInventoryService.php
│   │       └── ProductPricingService.php
│   ├── Domain/
│   │   ├── DTO/
│   │   │   └── ProductData.php
│   │   ├── Events/
│   │   │   ├── ProductCreated.php
│   │   │   ├── ProductPublished.php
│   │   │   └── StockLevelChanged.php
│   │   ├── Models/
│   │   │   ├── Product.php
│   │   │   ├── ProductVariant.php
│   │   │   ├── ProductPrice.php
│   │   │   └── ProductInventory.php
│   │   └── Repositories/
│   │       └── ProductRepository.php
│   └── Http/
│       ├── Controllers/Api/
│       ├── Requests/
│       └── Resources/
│
└── Events/                                  # Events Module
    ├── Application/
    │   └── Services/
    │       ├── EventQueryService.php
    │       └── EventRegistrationService.php
    ├── Domain/
    │   ├── DTO/
    │   │   └── EventData.php
    │   ├── Events/
    │   │   ├── EventCreated.php
    │   │   └── RegistrationReceived.php
    │   ├── Models/
    │   │   ├── Event.php
    │   │   ├── EventRegistration.php
    │   │   └── EventTicketType.php
    │   └── Repositories/
    │       └── EventRepository.php
    └── Http/
        ├── Controllers/Api/
        ├── Requests/
        └── Resources/
```

## المبادئ المعمارية

### 1. Single Responsibility Principle (SRP)
كل Service مسؤول عن مهمة واحدة فقط:
- **QueryService**: عمليات القراءة فقط
- **CommandService**: عمليات الكتابة (Create, Update, Delete)
- **WorkflowService**: تحولات الحالة (State Transitions)
- **TaxonomyService**: التصنيفات والوسوم
- **MediaService**: الوسائط والصور
- **CommentService**: إعدادات التعليقات

### 2. Action Classes
كل عملية مستقلة في Action Class منفصل:
```php
// بدلاً من method واحد كبير
$article = $articleService->create($data);

// نستخدم Action محدد
$article = $createArticleAction->execute($data);
```

### 3. Repository Pattern
```php
// الاستعلامات منفصلة عن Business Logic
$articles = $articleRepository->getPaginated($filters);
$article = $articleRepository->findBySlug($slug);
```

### 4. Data Transfer Objects (DTOs)
```php
// بدلاً من array
$data = ArticleData::fromRequest($request);
$article = $commandService->create($data);
```

### 5. State Machine
```php
// التحكم في تحولات الحالة
$state = ArticleState::fromArticle($article);
$state->transitionTo(PublishedState::class);
```

### 6. Domain Events
```php
// Events منفصلة للعمليات المهمة
event(new ArticleCreated($article));
event(new ArticlePublished($article));
event(new StockLevelChanged($product, $oldQty, $newQty, $reason));
```

## استخدام الهيكل الجديد

### في Controller
```php
public function __construct(
    private readonly ArticleQueryService $queryService,
    private readonly ArticleCommandService $commandService,
    private readonly ArticleWorkflowService $workflowService,
) {}

public function store(CreateArticleRequest $request): JsonResponse
{
    $data = ArticleData::fromRequest($request);
    $article = $this->commandService->create($data);
    return $this->created(new ArticleResource($article));
}

public function publish(string $id): JsonResponse
{
    $article = $this->queryService->find($id);
    $article = $this->commandService->publish($article);
    return $this->success(new ArticleResource($article));
}
```

### تسجيل الخدمات في ServiceProvider
```php
protected function registerServices(): void
{
    $this->app->singleton(ArticleRepository::class, fn ($app) => 
        new ArticleRepository(new Article())
    );
    
    $this->app->singleton(ArticleQueryService::class);
    $this->app->singleton(ArticleCommandService::class);
    $this->app->singleton(ArticleWorkflowService::class);
}
```

## التوافق العكسي

الـ Services القديمة لا تزال تعمل:
```php
// يمكن استخدام ArticleService القديم
$this->app->bind(ArticleServiceContract::class, ArticleService::class);
```

## Migration Guide

1. **Phase 1**: أضف الهيكل الجديد بجانب القديم
2. **Phase 2**: حوّل Controllers تدريجياً للهيكل الجديد
3. **Phase 3**: أزل الـ Services القديمة عند الانتهاء

## Commands

```bash
# تشغيل PHPStan للتحقق من الأخطاء
./vendor/bin/phpstan analyse modules/ --level=5

# تشغيل الاختبارات
php artisan test --filter=ArticleTest
```
