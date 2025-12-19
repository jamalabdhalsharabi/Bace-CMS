# 📋 المرحلة 4: تحسينات API

## الهدف
تحسين أمان وأداء الـ API عبر Rate Limiting, Error Handling موحد، و Response Headers.

---

## المهام

### 4.1 إنشاء Rate Limiting متقدم

**الملف:** `app/Providers/RouteServiceProvider.php`

```php
<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

// في دالة boot()
protected function configureRateLimiting(): void
{
    // Default API - 60 requests/minute
    RateLimiter::for('api', function ($request) {
        return Limit::perMinute(60)->by(
            $request->user()?->id ?: $request->ip()
        );
    });

    // Auth - 5 requests/minute (strict)
    RateLimiter::for('auth', function ($request) {
        return Limit::perMinute(5)->by($request->ip())
            ->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many attempts. Try again later.',
                ], 429);
            });
    });

    // Authenticated users - 120 requests/minute
    RateLimiter::for('authenticated', function ($request) {
        return $request->user()
            ? Limit::perMinute(120)->by($request->user()->id)
            : Limit::perMinute(30)->by($request->ip());
    });

    // Uploads - 20 requests/minute
    RateLimiter::for('uploads', function ($request) {
        return Limit::perMinute(20)->by(
            $request->user()?->id ?: $request->ip()
        );
    });

    // Search - 30 requests/minute
    RateLimiter::for('search', function ($request) {
        return Limit::perMinute(30)->by(
            $request->user()?->id ?: $request->ip()
        );
    });
}
```

---

### 4.2 إنشاء API Exception Handler

**الملف:** `modules/Core/Exceptions/ApiExceptionHandler.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    public function render(Throwable $e): JsonResponse
    {
        return match (true) {
            $e instanceof ValidationException => $this->validation($e),
            $e instanceof ModelNotFoundException => $this->modelNotFound($e),
            $e instanceof NotFoundHttpException => $this->notFound(),
            $e instanceof AuthenticationException => $this->unauthenticated(),
            default => $this->generic($e),
        };
    }

    protected function validation(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    }

    protected function modelNotFound(ModelNotFoundException $e): JsonResponse
    {
        $model = class_basename($e->getModel());
        return response()->json([
            'success' => false,
            'message' => "{$model} not found",
        ], 404);
    }

    protected function notFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Resource not found',
        ], 404);
    }

    protected function unauthenticated(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated',
        ], 401);
    }

    protected function generic(Throwable $e): JsonResponse
    {
        $message = config('app.debug') ? $e->getMessage() : 'Server error';
        
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 500);
    }
}
```

---

### 4.3 إنشاء API Response Middleware

**الملف:** `modules/Core/Http/Middleware/ApiResponseMiddleware.php`

```php
<?php

declare(strict_types=1);

namespace Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiResponseMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->header('X-API-Version', 'v2');
            $response->header('X-Request-ID', uniqid('req_'));
        }

        return $response;
    }
}
```

---

### 4.4 تطبيق Rate Limiting على Routes

**تحديث:** `modules/Content/Routes/api.php`

```php
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/v2')->middleware(['api', 'throttle:api'])->group(function () {
    
    // Public routes
    Route::middleware('throttle:api')->group(function () {
        Route::get('/articles', [ArticleController::class, 'index']);
        Route::get('/articles/{slug}', [ArticleController::class, 'show']);
    });

    // Protected routes
    Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
        Route::post('/articles', [ArticleController::class, 'store']);
        Route::put('/articles/{id}', [ArticleController::class, 'update']);
        Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);
    });

    // Search (separate rate limit)
    Route::middleware('throttle:search')->group(function () {
        Route::get('/search', [SearchController::class, 'search']);
    });
});
```

---

## ✅ قائمة التحقق

- [ ] إنشاء Rate Limiters متعددة
- [ ] إنشاء `ApiExceptionHandler`
- [ ] إنشاء `ApiResponseMiddleware`
- [ ] تطبيق Rate Limiting على Auth routes
- [ ] تطبيق Rate Limiting على Upload routes
- [ ] تطبيق Rate Limiting على Search routes
- [ ] إضافة X-Request-ID header
- [ ] إضافة X-API-Version header
- [ ] اختبار Rate Limiters

---

## 📊 Rate Limits المقترحة

| Endpoint | Rate Limit | السبب |
|----------|------------|-------|
| `/api/*` | 60/min | افتراضي |
| `/api/auth/*` | 5/min | حماية من brute force |
| `/api/*/upload` | 20/min | حماية الموارد |
| `/api/search` | 30/min | حماية من إساءة الاستخدام |
| Authenticated | 120/min | المستخدمين المسجلين |

---

## 🧪 اختبار المرحلة

```bash
# اختبار Rate Limiting
for i in {1..70}; do curl -s http://localhost/api/v2/articles > /dev/null; done

# التحقق من Headers
curl -I http://localhost/api/v2/articles
# يجب أن يظهر: X-API-Version: v2
```
