# تحليل عمليات أسعار الصرف (Exchange Rates Operations)

## 📋 نظرة عامة
أسعار الصرف تدير تحويلات العملات في النظام. تدعم التحديث التلقائي من APIs خارجية، التجميد، والتاريخ.

---

## 🔄 State Machine Diagram

```
┌──────────┐   create   ┌──────────┐
│  (new)   │───────────▶│  active  │
└──────────┘            └──────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
       ┌───────────┐   ┌───────────┐   ┌───────────┐
       │  frozen   │   │  expired  │   │  deleted  │
       └───────────┘   └───────────┘   └───────────┘
              │
              ▼
       ┌───────────┐
       │  active   │
       └───────────┘
```

---

## 📌 العملية 1: جلب أسعار الصرف من API (Fetch Exchange Rates)

### 1. اسم العملية
`exchange_rate.fetch`

### 2. الهدف
جلب أسعار الصرف الحالية من مزود خارجي.

### 3. الشروط المسبقة
- API key صالح
- العملة المصدر موجودة
- الاتصال بالإنترنت متاح

### 4. خطوات التنفيذ

```
[1] Get Active Currencies
    └── SELECT * FROM currencies WHERE status = 'active'

[2] Prepare API Request
    ├── Get base currency
    └── Build target currencies list

[3] Call External API
    ├── Primary: Open Exchange Rates / Fixer.io / ExchangeRate-API
    └── Fallback: Secondary provider

[4] Parse Response
    └── Extract rates for each currency pair

[5] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [6] For each currency pair:
    │   │
    │   [7] Check if rate exists
    │   │
    │   [8] If exists and not frozen:
    │   │   ├── Archive current rate
    │   │   │   └── INSERT INTO exchange_rate_history
    │   │   └── Update rate
    │   │       └── UPDATE exchange_rates SET rate = ?, updated_at = ?
    │   │
    │   [9] If not exists:
    │       └── INSERT INTO exchange_rates
    │
    [10] Log fetch operation
    │    └── INSERT INTO exchange_rate_logs
    │
COMMIT TRANSACTION ───────────────────────────────────

[11] Dispatch Events
     └── ExchangeRatesUpdated event

[12] Queue Jobs
     ├── InvalidatePriceCacheJob
     ├── RecalculateDisplayPricesJob
     └── NotifyRateChangeSubscribersJob (if significant change)
```

### 5. الآثار الجانبية
- تحديث أسعار الصرف
- أرشفة الأسعار القديمة
- تحديث الأسعار المعروضة

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| API Timeout | Retry with backoff, use fallback |
| API Error | Log, use last known rate |
| Invalid Response | Log, skip update |
| Rate Limit | Wait and retry |
| All Providers Failed | Alert admin, keep old rates |

### 7. Idempotency & Concurrency
- تسجيل آخر وقت جلب
- منع الجلب المتكرر (rate limiting)
- قفل متفائل على الأسعار

### 8. Security Considerations
- تخزين API keys مشفرة
- استخدام HTTPS فقط
- التحقق من صحة البيانات
- Rate limiting على API calls

### 9. Observability

```yaml
metrics:
  - exchange_rate.fetch.count
  - exchange_rate.fetch.duration_ms
  - exchange_rate.fetch.failures
  - exchange_rate.change_percentage

logs:
  fields:
    - provider: {provider_name}
    - currencies_updated: N
    - base_currency: {code}
    - fetch_duration_ms: N

alerts:
  - condition: fetch_failures > 3 consecutive
    severity: critical
  - condition: rate_change > 10% in 1 hour
    severity: warning
```

### 10. External Dependencies
- Open Exchange Rates API
- Fixer.io API
- ExchangeRate-API
- European Central Bank (fallback)

### 11. API Endpoint (Internal/Admin)

```http
POST /api/v1/exchange-rates/fetch
Authorization: Bearer {token}

{
  "base_currency": "USD",
  "target_currencies": ["EUR", "SAR", "AED"],
  "provider": "openexchangerates"
}
```

**Response:**
```json
{
  "success": true,
  "rates_updated": 3,
  "timestamp": "2024-01-15T10:00:00Z",
  "rates": {
    "USD_EUR": 0.92,
    "USD_SAR": 3.75,
    "USD_AED": 3.67
  }
}
```

### 12. Webhook Payload

```json
{
  "event": "exchange_rates.updated",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "base_currency": "USD",
    "rates": {
      "EUR": { "rate": 0.92, "previous": 0.91, "change_percent": 1.1 },
      "SAR": { "rate": 3.75, "previous": 3.75, "change_percent": 0 }
    },
    "source": "openexchangerates"
  }
}
```

---

## 📌 العملية 2: جدولة تحديث أسعار الصرف (Schedule Sync)

### 1. اسم العملية
`exchange_rate.schedule_sync`

### 2. الهدف
إعداد جدولة تلقائية لتحديث أسعار الصرف.

### 3. خطوات التنفيذ

```
[1] Define Schedule
    ├── Frequency: hourly / daily / custom
    ├── Time: specific time for daily
    └── Timezone: system timezone

[2] Create Scheduled Task
    └── Register in task scheduler

[3] Store Schedule Settings
    └── UPDATE system_settings SET exchange_rate_schedule = ?

[4] Queue First Sync (if immediate start)
```

### 4. API Endpoint

```http
PUT /api/v1/exchange-rates/schedule
{
  "enabled": true,
  "frequency": "hourly",
  "specific_times": ["09:00", "17:00"],
  "timezone": "Asia/Riyadh",
  "notify_on_failure": true
}
```

---

## 📌 العملية 3: تعديل سعر الصرف يدوياً (Manual Rate Update)

### 1. اسم العملية
`exchange_rate.manual_update`

### 2. الهدف
تحديث سعر صرف يدوياً بواسطة المسؤول.

### 3. الشروط المسبقة
- صلاحية `exchange_rate.update`
- السعر غير مجمد
- القيمة صالحة (> 0)

### 4. خطوات التنفيذ

```
[1] Validate new rate
    ├── Rate > 0
    └── Reasonable change (< 50% from current)

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Archive current rate
    │   └── INSERT INTO exchange_rate_history
    │
    [4] Update rate
    │   └── UPDATE exchange_rates SET rate = ?, source = 'manual', updated_by = ?
    │
    [5] Log manual change
    │   └── INSERT INTO exchange_rate_logs (type = 'manual')
    │
COMMIT ───────────────────────────────────────────────

[6] Dispatch ExchangeRateManuallyUpdated event

[7] Queue RecalculateAffectedPricesJob
```

### 5. API Endpoint

```http
PUT /api/v1/exchange-rates/{from}/{to}
{
  "rate": 3.76,
  "reason": "Market adjustment",
  "effective_from": "2024-01-15T12:00:00Z"
}
```

---

## 📌 العملية 4: تجميد سعر الصرف (Freeze Rate)

### 1. اسم العملية
`exchange_rate.freeze`

### 2. الهدف
تجميد سعر صرف لمنع التحديث التلقائي.

### 3. استخدامات التجميد
- عروض ترويجية ثابتة السعر
- عقود بسعر محدد
- فترات عدم الاستقرار

### 4. خطوات التنفيذ

```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update rate status → frozen
    │
    [3] Set frozen_at, frozen_by, frozen_until
    │
    [4] Store freeze reason
    │
COMMIT ───────────────────────────────────────────────

[5] Schedule UnfreezeJob (if frozen_until set)

[6] Dispatch ExchangeRateFrozen event
```

### 5. API Endpoint

```http
POST /api/v1/exchange-rates/{from}/{to}/freeze
{
  "reason": "Promotional campaign",
  "until": "2024-02-01T00:00:00Z"
}
```

---

## 📌 العملية 5: إلغاء تجميد سعر الصرف (Unfreeze Rate)

```http
POST /api/v1/exchange-rates/{from}/{to}/unfreeze
{
  "update_rate": true
}
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update rate status → active
    │
    [3] Clear frozen fields
    │
    [4] If update_rate:
    │   └── Queue FetchCurrentRateJob
    │
COMMIT ───────────────────────────────────────────────
```

---

## 📌 العملية 6: عرض تاريخ الأسعار (View Rate History)

```http
GET /api/v1/exchange-rates/{from}/{to}/history
?from_date=2024-01-01
&to_date=2024-01-31
&interval=daily
```

**Response:**
```json
{
  "from_currency": "USD",
  "to_currency": "SAR",
  "history": [
    { "date": "2024-01-01", "rate": 3.75, "source": "api" },
    { "date": "2024-01-02", "rate": 3.74, "source": "api" },
    { "date": "2024-01-03", "rate": 3.76, "source": "manual" }
  ],
  "statistics": {
    "min": 3.74,
    "max": 3.76,
    "avg": 3.75,
    "volatility": 0.27
  }
}
```

---

## 📌 العملية 7: تنظيف الأسعار القديمة (Cleanup Old Rates)

### 1. اسم العملية
`exchange_rate.cleanup`

### 2. الهدف
حذف بيانات تاريخ أسعار الصرف القديمة للحفاظ على الأداء.

### 3. خطوات التنفيذ

```
[Scheduled Job - Monthly]

[1] Determine retention period
    └── Default: 1 year

[2] Aggregate old data
    └── Create monthly averages for old data

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Insert aggregated records
    │   └── INSERT INTO exchange_rate_aggregates
    │
    [5] Delete detailed old records
    │   └── DELETE FROM exchange_rate_history WHERE date < ?
    │
COMMIT ───────────────────────────────────────────────

[6] Log cleanup operation
```

### 4. API Endpoint (Manual Trigger)

```http
POST /api/v1/exchange-rates/cleanup
{
  "older_than_days": 365,
  "aggregate": true,
  "dry_run": false
}
```

---

## 📌 العملية 8: التعامل مع فشل API (Handle API Failure)

```
[Automatic Process]

[1] On API failure:
    ├── Increment failure counter
    ├── Log error details
    └── Try fallback provider

[2] If all providers fail:
    ├── Use last known rates
    ├── Mark rates as stale
    └── Alert administrators

[3] Recovery process:
    ├── Retry with exponential backoff
    ├── On success: clear failure counter
    └── Resume normal schedule
```

---

## 📌 العملية 9: كشف تعارض الأسعار (Detect Rate Conflicts)

```
[Automatic Check]

[1] Compare rates from multiple providers

[2] If difference > threshold (e.g., 2%):
    ├── Flag as conflict
    ├── Use median value
    └── Alert for review

[3] Log discrepancy for analysis
```

---

## 📌 العملية 10: تحديث تأثير على المنتجات (Update Product Impact)

```
[Triggered after rate update]

[1] Get products with prices in affected currencies

[2] For each product:
    │
    [3] Recalculate display prices
    │   └── base_price * new_rate
    │
    [4] Update price cache
    │
    [5] Check price alerts
    │   └── Notify users if price crossed threshold

[6] Queue ReindexProductsJob
```

---

## 📌 العملية 11: إنشاء تنبيه سعر (Create Rate Alert)

```http
POST /api/v1/exchange-rates/alerts
{
  "from_currency": "USD",
  "to_currency": "EUR",
  "condition": "above",
  "threshold": 0.95,
  "notify_channels": ["email", "slack"]
}
```

---

## 📌 العملية 12: استيراد أسعار تاريخية (Import Historical Rates)

```http
POST /api/v1/exchange-rates/import-history
Content-Type: multipart/form-data

file: rates.csv
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                  Exchange Rate Lifecycle Flow                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Scheduler        System           API           Products       │
│    │                │               │               │            │
│    │── Trigger ────▶│               │               │            │
│    │                │── Fetch ─────▶│               │            │
│    │                │◀── Rates ─────│               │            │
│    │                │               │               │            │
│    │                │── Archive ────│               │            │
│    │                │── Update ─────│               │            │
│    │                │               │               │            │
│    │                │── Recalculate────────────────▶│            │
│    │                │   Prices      │               │            │
│    │                │               │               │            │
│    │                │── Notify ─────│               │            │
│    │                │   (if major   │               │            │
│    │                │    change)    │               │            │
│    │                │               │               │            │
│  Admin             │               │               │            │
│    │── Freeze ────▶│               │               │            │
│    │                │── Skip ──────X│               │            │
│    │                │   Updates     │               │            │
│    │                │               │               │            │
│    │── Unfreeze ──▶│               │               │            │
│    │                │── Resume ────▶│               │            │
│    │                │               │               │            │
│    │                │               │               │            │
│  Cleanup           │               │               │            │
│    │── Monthly ───▶│               │               │            │
│    │                │── Aggregate ──│               │            │
│    │                │── Delete Old ─│               │            │
│    │                │               │               │            │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات أسعار الصرف**
