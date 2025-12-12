# تحليل عمليات العملات (Currencies Operations)

## 📋 نظرة عامة
العملات هي كيانات أساسية للتعامل مع التسعير متعدد العملات. تدعم التحويل التلقائي، التنسيق المحلي، والتكامل مع أنظمة الدفع.

---

## 🔄 State Machine Diagram

```
┌──────────┐   create   ┌──────────┐
│  (new)   │───────────▶│ inactive │
└──────────┘            └──────────┘
                              │
                              ▼
                        ┌───────────┐
                        │  active   │
                        └───────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
       ┌───────────┐   ┌───────────┐   ┌──────────────┐
       │ inactive  │   │  default  │   │ soft_deleted │
       └───────────┘   └───────────┘   └──────────────┘
```

---

## 📌 العملية 1: إنشاء عملة جديدة (Create Currency)

### 1. اسم العملية
`currency.create`

### 2. الهدف
إضافة عملة جديدة للنظام مع إعداداتها.

### 3. الشروط المسبقة
- صلاحية `currency.create`
- رمز العملة (ISO 4217) فريد
- إعدادات التنسيق صالحة

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate ISO code (3 letters)
    ├── Validate code uniqueness
    ├── Validate decimal places (0-4)
    └── Validate symbol

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Generate UUID
    │
    [4] Create Currency Record
    │   └── INSERT INTO currencies (id, code, symbol, name, decimal_places, decimal_separator, thousands_separator, symbol_position, status, ...)
    │
    [5] Create Translation Records
    │   └── INSERT INTO currency_translations (currency_id, locale, name, ...)
    │
    [6] Create Initial Exchange Rate (to base currency)
    │   └── INSERT INTO exchange_rates (from_currency_id, to_currency_id, rate, ...)
    │
    [7] Set Default Rounding Rules
    │   └── INSERT INTO currency_settings (currency_id, key, value)
    │
COMMIT TRANSACTION ───────────────────────────────────

[8] Dispatch Events
    └── CurrencyCreated event

[9] Queue Jobs
    ├── FetchExchangeRateJob
    └── InvalidateCurrencyCacheJob
```

### 5. الآثار الجانبية
- إنشاء سجل العملة
- إنشاء سعر صرف أولي
- تحديث قائمة العملات المتاحة

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Duplicate Code | Return 422 |
| Invalid ISO Code | Return 422 + valid codes |
| Exchange Rate Fetch Failed | Create with manual rate |

### 7. Security Considerations
- صلاحية خاصة لإدارة العملات
- تسجيل جميع التغييرات
- التحقق من صحة رمز ISO

### 8. Observability

```yaml
metrics:
  - currency.create.count
  - currency.active.count
  - currency.conversion.count

logs:
  fields:
    - code: {iso_code}
    - symbol: {symbol}
    - created_by: {user_id}
```

### 9. Roles & Permissions
| الدور | الصلاحية |
|------|---------|
| Super Admin | ✅ |
| Admin | ✅ |
| Finance Manager | ✅ |
| Others | ❌ |

### 10. API Endpoint

```http
POST /api/v1/currencies
Authorization: Bearer {token}
Content-Type: application/json

{
  "code": "SAR",
  "symbol": "ر.س",
  "translations": {
    "ar": { "name": "ريال سعودي" },
    "en": { "name": "Saudi Riyal" }
  },
  "decimal_places": 2,
  "decimal_separator": ".",
  "thousands_separator": ",",
  "symbol_position": "after",
  "rounding": {
    "method": "half_up",
    "precision": 2
  },
  "status": "active"
}
```

### 11. Webhook Payload

```json
{
  "event": "currency.created",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "id": "uuid",
    "code": "SAR",
    "symbol": "ر.س",
    "status": "active"
  }
}
```

---

## 📌 العملية 2: تعديل عملة (Update Currency)

### 1. اسم العملية
`currency.update`

### 2. خطوات التنفيذ

```
[1] Load currency

[2] Validate changes
    └── Code cannot be changed if has transactions

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Update Currency Record
    │
    [5] Sync Translations
    │
    [6] Update Settings
    │
COMMIT ───────────────────────────────────────────────

[7] Queue Jobs
    ├── InvalidateCurrencyCacheJob
    ├── RecalculatePricesJob (if formatting changed)
    └── UpdateDisplayedPricesJob
```

### 3. Implementation Notes
- تغيير رمز العملة يتطلب تأكيد إضافي
- تغيير المنازل العشرية يؤثر على العرض فقط

---

## 📌 العملية 3: تفعيل عملة (Enable Currency)

```http
POST /api/v1/currencies/{id}/enable
```

**خطوات التنفيذ:**
```
[1] Check exchange rate exists

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → active
    │
    [4] Set enabled_at
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs
    ├── UpdateAvailableCurrenciesJob
    ├── InvalidateCacheJob
    └── SyncToPaymentGatewaysJob
```

---

## 📌 العملية 4: تعطيل عملة (Disable Currency)

```http
POST /api/v1/currencies/{id}/disable
{
  "convert_prices_to": "USD"
}
```

**خطوات التنفيذ:**
```
[1] Check not default currency

[2] Check no pending transactions

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Update status → inactive
    │
    [5] Optionally convert existing prices
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs
    ├── UpdateAvailableCurrenciesJob
    ├── NotifyAffectedUsersJob
    └── ConvertPendingPricesJob
```

---

## 📌 العملية 5: تعيين العملة الافتراضية (Set Default Currency)

```http
POST /api/v1/currencies/{id}/set-default
{
  "recalculate_prices": true
}
```

**خطوات التنفيذ:**
```
[1] Validate currency is active

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Remove default from current
    │   └── UPDATE currencies SET is_default = false WHERE is_default = true
    │
    [4] Set new default
    │   └── UPDATE currencies SET is_default = true WHERE id = ?
    │
    [5] Update system settings
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs (CRITICAL)
    ├── RecalculateAllExchangeRatesJob
    ├── UpdateBasePricesJob (if recalculate)
    ├── InvalidateAllPriceCachesJob
    └── NotifyIntegrationsJob
```

### Implementation Notes
- العملة الافتراضية هي أساس حسابات أسعار الصرف
- تغييرها يتطلب إعادة حساب جميع الأسعار

---

## 📌 العملية 6: ترتيب عرض العملات (Reorder Currencies)

```http
PUT /api/v1/currencies/reorder
{
  "order": ["USD", "EUR", "SAR", "AED"]
}
```

---

## 📌 العملية 7: حذف عملة (Delete Currency)

```http
DELETE /api/v1/currencies/{id}
{
  "migrate_prices_to": "USD",
  "force": false
}
```

**خطوات التنفيذ:**
```
[1] Check not default currency

[2] Check no active transactions

[3] If has prices:
    ├── If migrate_to specified → convert
    └── Else → return error

[4] BEGIN TRANSACTION ────────────────────────────────
    │
    [5] Migrate prices (if needed)
    │
    [6] Delete exchange rates
    │
    [7] Soft delete currency
    │
COMMIT ───────────────────────────────────────────────

[8] Queue CleanupCurrencyDataJob
```

---

## 📌 العملية 8: تنسيق المبلغ (Format Amount)

```
[Internal Operation]

Input: amount = 1234.567, currency = "SAR"

[1] Load currency settings
    ├── decimal_places: 2
    ├── decimal_separator: "."
    ├── thousands_separator: ","
    └── symbol_position: "after"

[2] Apply rounding
    └── 1234.57

[3] Format number
    └── "1,234.57"

[4] Add symbol
    └── "1,234.57 ر.س"
```

---

## 📌 العملية 9: تحويل العملة (Convert Currency)

```http
GET /api/v1/currencies/convert?from=USD&to=SAR&amount=100
```

**خطوات التنفيذ:**
```
[1] Get exchange rate

[2] Calculate converted amount
    └── amount * rate

[3] Apply rounding

[4] Return result
```

**Response:**
```json
{
  "from": { "currency": "USD", "amount": 100 },
  "to": { "currency": "SAR", "amount": 375.00 },
  "rate": 3.75,
  "rate_timestamp": "2024-01-15T10:00:00Z"
}
```

---

## 📌 العملية 10: استيراد عملات (Import Currencies)

```http
POST /api/v1/currencies/import
{
  "source": "iso_4217",
  "currencies": ["USD", "EUR", "GBP", "SAR", "AED"]
}
```

---

## 📌 العملية 11: تحديث تأثير على المنتجات (Update Product Prices)

```
[Triggered when currency settings change]

[1] Get affected products

[2] For each product:
    │
    [3] Recalculate display price
    │
    [4] Update price formatting
    │
    [5] Invalidate product cache

[6] Queue ReindexProductsJob
```

---

## 📌 العملية 12: مزامنة مع بوابات الدفع

```http
POST /api/v1/currencies/sync-payment-gateways
```

**خطوات التنفيذ:**
```
[1] Get active payment gateways

[2] For each gateway:
    │
    [3] Get supported currencies
    │
    [4] Update local currency-gateway mapping
    │
    [5] Flag unsupported currencies
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                    Currency Lifecycle Flow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Admin           System          Products       Payment         │
│    │               │                │              │             │
│    │── Create ────▶│                │              │             │
│    │               │── Fetch Rate ──│              │             │
│    │               │                │              │             │
│    │── Enable ────▶│                │              │             │
│    │               │── Update ─────▶│              │             │
│    │               │   Available    │              │             │
│    │               │── Sync ───────────────────────▶│             │
│    │               │                │              │             │
│    │── Set Default▶│                │              │             │
│    │               │── Recalculate ▶│              │             │
│    │               │   Prices       │              │             │
│    │               │                │              │             │
│    │               │        ◀── Convert ──         │             │
│    │               │── Return Rate ─│              │             │
│    │               │                │              │             │
│    │── Disable ───▶│                │              │             │
│    │               │── Notify ─────▶│              │             │
│    │               │── Update ─────────────────────▶│             │
│    │               │                │              │             │
│    │── Delete ────▶│                │              │             │
│    │               │── Migrate ────▶│              │             │
│    │               │   Prices       │              │             │
│    │               │                │              │             │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات العملات**
