# تحليل عمليات المنتجات (Product Operations)

## 📋 نظرة عامة
المنتجات هي كيانات تجارية تدعم المخزون، التسعير المتعدد العملات، المتغيرات، والعروض. تتكامل مع أنظمة الدفع والشحن.

---

## 🔄 State Machine Diagram

```
┌──────────┐    save     ┌──────────┐   submit    ┌─────────────────┐
│  (new)   │────────────▶│  draft   │────────────▶│ pending_review  │
└──────────┘             └──────────┘             └─────────────────┘
                              ▲                          │
                              │                    ┌─────┴─────┐
                              │                    ▼           ▼
                         ┌────────┐          ┌──────────┐ ┌──────────┐
                         │rejected│◀─────────│in_review │ │ approved │
                         └────────┘          └──────────┘ └──────────┘
                                                               │
                                                               ▼
                                                        ┌───────────┐
                                                        │ published │
                                                        └───────────┘
                                                               │
                              ┌─────────────────┬──────────────┼──────────────┐
                              ▼                 ▼              ▼              ▼
                        ┌───────────┐    ┌───────────┐  ┌────────────┐ ┌──────────────┐
                        │out_of_stock│    │unpublished│  │discontinued│ │ soft_deleted │
                        └───────────┘    └───────────┘  └────────────┘ └──────────────┘
                              │                                │
                              ▼                                ▼
                        ┌───────────┐                   ┌───────────┐
                        │back_in_stock│                 │ archived  │
                        └───────────┘                   └───────────┘
```

---

## 📌 العملية 1: إنشاء منتج (Create Product)

### 1. اسم العملية
`product.create`

### 2. الهدف
إنشاء منتج جديد مع دعم المتغيرات والتسعير المتعدد العملات.

### 3. الشروط المسبقة
- صلاحية `product.create`
- التصنيفات موجودة
- الأسعار صالحة
- SKU فريد

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate SKU uniqueness
    ├── Validate prices per currency
    ├── Validate stock quantities
    ├── Validate variants consistency
    └── Validate category assignments

[2] Authorization Check
    └── Gate::authorize('product.create')

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Generate UUID and SKU (if auto)
    │
    [5] Create Product Record
    │   └── INSERT INTO products (id, sku, type, status, ...)
    │
    [6] Create Translation Records
    │   └── INSERT INTO product_translations (product_id, locale, name, slug, description, ...)
    │
    [7] Create Price Records
    │   └── INSERT INTO product_prices (product_id, currency_id, amount, compare_at, ...)
    │
    [8] Create Inventory Record
    │   └── INSERT INTO product_inventory (product_id, quantity, low_stock_threshold, ...)
    │
    [9] Create Variants (if applicable)
    │   ├── INSERT INTO product_variants (product_id, sku, ...)
    │   ├── INSERT INTO variant_prices
    │   └── INSERT INTO variant_inventory
    │
    [10] Create Initial Revision
    │
    [11] Sync Categories
    │
    [12] Sync Tags
    │
    [13] Process Media
    │    ├── Main images
    │    ├── Variant images
    │    └── Documents (specs, manuals)
    │
COMMIT TRANSACTION ───────────────────────────────────

[14] Dispatch Events
     └── ProductCreated event

[15] Queue Jobs
     ├── CalculatePriceRangeJob
     ├── IndexSearchJob
     └── SyncToExternalChannelsJob (if configured)
```

### 5. الآثار الجانبية
- إنشاء سجل المنتج
- إنشاء سجلات الأسعار
- إنشاء سجل المخزون
- تحديث فهرس البحث

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Duplicate SKU | Return 422 + suggested SKU |
| Invalid Price | Return 422 + currency details |
| Category Not Found | Return 422 |
| Variant Conflict | Return 422 + conflict details |

### 7. Idempotency & Concurrency
- `X-Idempotency-Key` header
- SKU uniqueness check
- Price creation with upsert per currency

### 8. Security Considerations
- التحقق من صلاحية تحديد الأسعار
- منع الأسعار السالبة
- التحقق من صلاحية الوصول للتصنيفات
- تشفير بيانات التكلفة الحساسة

### 9. Observability

```yaml
metrics:
  - product.create.count
  - product.create.duration_ms
  - product.create.by_type
  - product.create.with_variants

logs:
  fields:
    - sku: {sku}
    - type: {type}
    - variant_count: N
    - currency_count: N
    - initial_stock: N
```

### 10. Roles & Permissions
| الدور | الصلاحية |
|------|---------|
| Super Admin | ✅ |
| Admin | ✅ |
| Product Manager | ✅ |
| Editor | ❌ |

### 11. External Dependencies
- Inventory System
- Pricing Engine
- Tax Calculator
- External Channels (optional)

### 12. API Endpoint

```http
POST /api/v1/products
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "physical",
  "sku": "PROD-001",
  "translations": {
    "ar": { "name": "اسم المنتج", "slug": "product-name", "description": "..." },
    "en": { "name": "Product Name", "slug": "product-name", "description": "..." }
  },
  "prices": [
    { "currency": "USD", "amount": 99.99, "compare_at": 129.99 },
    { "currency": "EUR", "amount": 89.99 }
  ],
  "inventory": {
    "quantity": 100,
    "low_stock_threshold": 10,
    "track_inventory": true
  },
  "variants": [
    {
      "sku": "PROD-001-RED-S",
      "options": { "color": "red", "size": "S" },
      "price_modifier": 0,
      "inventory": { "quantity": 25 }
    }
  ],
  "categories": ["uuid1"],
  "media": [
    { "id": "media-uuid", "collection": "gallery", "order": 1 }
  ]
}
```

### 13. Webhook Payload

```json
{
  "event": "product.created",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "id": "uuid",
    "sku": "PROD-001",
    "type": "physical",
    "variant_count": 4,
    "price_range": {
      "min": { "amount": 89.99, "currency": "EUR" },
      "max": { "amount": 99.99, "currency": "USD" }
    },
    "initial_stock": 100
  }
}
```

---

## 📌 العملية 2: تعديل منتج (Update Product)

### 1. اسم العملية
`product.update`

### 2. الهدف
تحديث بيانات المنتج مع مزامنة الأسعار والمخزون.

### 3. خطوات التنفيذ

```
[1] Load product with lock

[2] Validate changes
    ├── SKU change restrictions (if has orders)
    ├── Price change validation
    └── Variant modification rules

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Create Revision
    │
    [5] Update Product Record
    │
    [6] Sync Translations
    │
    [7] Sync Prices ──────────────────────────────────
    │   ├── Track price history
    │   └── Update comparison prices
    │
    [8] Sync Variants
    │   ├── Add new variants
    │   ├── Update existing
    │   └── Soft-delete removed
    │
    [9] Update Media
    │
COMMIT TRANSACTION ───────────────────────────────────

[10] Queue Jobs
     ├── RecalculatePriceRangeJob
     ├── ReindexSearchJob
     ├── InvalidateCacheJob
     ├── SyncToExternalChannelsJob
     └── NotifyPriceWatchersJob (if price changed)
```

### 4. Implementation Notes
- تتبع تاريخ الأسعار
- إشعار المستخدمين المهتمين عند تغيير السعر
- منع تعديل SKU إذا كان له طلبات

---

## 📌 العمليات 3-7: Draft, Review, Approve, Reject

*نفس النمط العام*

---

## 📌 العملية 8: النشر (Publish Product)

### 1. اسم العملية
`product.publish`

### 2. الهدف
نشر المنتج وإتاحته للبيع.

### 3. الشروط المسبقة
- على الأقل سعر واحد محدد
- على الأقل صورة واحدة
- مخزون > 0 أو backorder مفعل
- وصف مكتمل

### 4. خطوات التنفيذ

```
[1] Validate publishable
    ├── Has price in default currency
    ├── Has featured image
    ├── Has description
    └── Has stock or allows backorder

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Update status → published
    │
    [4] Set published_at
    │
    [5] Make prices active
    │
    [6] Create Revision (type: publish)
    │
COMMIT TRANSACTION ───────────────────────────────────

[7] Queue Jobs (HIGH PRIORITY)
    ├── IndexSearchJob
    ├── InvalidateCacheJob
    ├── UpdateSitemapJob
    ├── SyncToMarketplacesJob
    ├── GenerateProductFeedJob (Google, Facebook)
    └── NotifyWishlistUsersJob
```

### 5. الآثار الجانبية
- المنتج متاح للشراء
- ظهور في البحث والفلاتر
- مزامنة مع الأسواق الخارجية
- إشعار المستخدمين بقوائم الرغبات

---

## 📌 العمليات الخاصة بالمنتجات

### 9. إدارة المخزون (Inventory Management)

#### 9.1 تحديث الكمية (Update Stock)
```http
PUT /api/v1/products/{id}/inventory
{
  "quantity": 150,
  "reason": "restock",
  "reference": "PO-2024-001"
}
```

**خطوات التنفيذ:**
```
[1] Validate quantity >= 0

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Get current quantity
    │
    [4] Calculate adjustment
    │
    [5] Update inventory
    │   └── UPDATE product_inventory SET quantity = ?
    │
    [6] Log inventory movement
    │   └── INSERT INTO inventory_movements (product_id, type, quantity, reason, reference, ...)
    │
    [7] Check stock status
    │   ├── If was out_of_stock and now > 0 → back_in_stock
    │   └── If now <= low_threshold → low_stock
    │
COMMIT ───────────────────────────────────────────────

[8] Dispatch Events
    ├── InventoryUpdated
    ├── BackInStock (if applicable)
    └── LowStock (if applicable)

[9] Queue Jobs
    └── NotifyBackInStockSubscribersJob
```

#### 9.2 حجز المخزون (Reserve Stock)
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Check available quantity
    │   └── available = quantity - reserved
    │
    [3] If available >= requested
    │   └── UPDATE SET reserved = reserved + requested
    │
    [4] Create reservation record
    │   └── INSERT INTO stock_reservations (product_id, order_id, quantity, expires_at)
    │
COMMIT ───────────────────────────────────────────────

[5] Schedule ReleaseReservationJob (at expiry)
```

#### 9.3 تحرير الحجز (Release Reservation)
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] UPDATE SET reserved = reserved - quantity
    │
    [3] DELETE FROM stock_reservations WHERE id = ?
    │
COMMIT ───────────────────────────────────────────────
```

#### 9.4 تأكيد الخصم (Confirm Deduction)
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] UPDATE SET quantity = quantity - reserved_amount
    │
    [3] UPDATE SET reserved = reserved - reserved_amount
    │
    [4] DELETE reservation
    │
    [5] Log movement (type: sale)
    │
COMMIT ───────────────────────────────────────────────
```

### 10. إدارة الأسعار (Pricing Management)

#### 10.1 تحديث السعر (Update Price)
```http
PUT /api/v1/products/{id}/prices/{currency}
{
  "amount": 89.99,
  "compare_at": 109.99,
  "effective_from": "2024-02-01T00:00:00Z"
}
```

**خطوات التنفيذ:**
```
[1] Validate price > 0

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Archive current price
    │   └── INSERT INTO price_history
    │
    [4] Update or schedule new price
    │   ├── If immediate: UPDATE product_prices
    │   └── If scheduled: INSERT INTO scheduled_prices
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs
    ├── RecalculatePriceRangeJob
    └── NotifyPriceWatchersJob
```

#### 10.2 تحويل العملة (Currency Conversion)
```
[1] Get base price

[2] Get exchange rate
    └── From exchange_rates table

[3] Calculate converted price
    └── Apply rounding rules per currency

[4] Return or store converted price
```

#### 10.3 تطبيق خصم (Apply Discount)
```http
POST /api/v1/products/{id}/discount
{
  "type": "percentage",
  "value": 20,
  "starts_at": "2024-01-20",
  "ends_at": "2024-01-27"
}
```

### 11. إدارة المتغيرات (Variant Management)

#### 11.1 إضافة متغير (Add Variant)
```http
POST /api/v1/products/{id}/variants
{
  "sku": "PROD-001-BLUE-M",
  "options": { "color": "blue", "size": "M" },
  "prices": [...],
  "inventory": { "quantity": 50 }
}
```

#### 11.2 تعطيل متغير (Disable Variant)
```http
POST /api/v1/products/{id}/variants/{variant_id}/disable
```

#### 11.3 حذف متغير (Delete Variant)
```
[1] Check no pending orders

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Soft delete variant
    │
    [4] Release reserved stock
    │
    [5] Update product variant count
    │
COMMIT ───────────────────────────────────────────────
```

### 12. نفاد المخزون (Out of Stock)

#### 12.1 تعيين نفاد تلقائي
```
[Triggered by inventory update]

[1] If quantity <= 0 AND not allowing backorder
    │
    [2] Update product status → out_of_stock
    │
    [3] Remove from active listings
    │
    [4] Notify product manager
    │
    [5] Show "Out of Stock" on frontend
```

#### 12.2 إدارة Backorder
```http
PUT /api/v1/products/{id}/backorder
{
  "allow_backorder": true,
  "backorder_limit": 50,
  "expected_restock_date": "2024-02-15"
}
```

### 13. إيقاف المنتج (Discontinue Product)

```http
POST /api/v1/products/{id}/discontinue
{
  "reason": "end_of_life",
  "replacement_product_id": "uuid",
  "clear_stock": false
}
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status → discontinued
    │
    [3] Set discontinued_at
    │
    [4] Link replacement product (if any)
    │
    [5] Optionally clear remaining stock
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs
    ├── RemoveFromSearchJob
    ├── UpdateProductFeedsJob
    ├── NotifySubscribersJob
    └── CreateRedirectJob (to replacement)
```

### 14. استنساخ المنتج (Clone Product)

```http
POST /api/v1/products/{id}/clone
{
  "new_sku": "PROD-002",
  "include_prices": true,
  "include_variants": true,
  "include_media": true
}
```

### 15. استيراد/تصدير المنتجات (Import/Export)

#### 15.1 تصدير
```http
POST /api/v1/products/export
{
  "format": "csv",
  "filters": { "category": "uuid" },
  "fields": ["sku", "name", "price", "stock"]
}
```

**خطوات التنفيذ:**
```
[1] Queue ExportProductsJob

[2] Job execution:
    ├── Fetch products in batches
    ├── Transform to export format
    ├── Write to temporary file
    └── Upload to storage

[3] Notify user with download link
```

#### 15.2 استيراد
```http
POST /api/v1/products/import
Content-Type: multipart/form-data

file: products.csv
options: { "update_existing": true, "skip_errors": false }
```

**خطوات التنفيذ:**
```
[1] Validate file format

[2] Queue ImportProductsJob

[3] Job execution:
    ├── Parse file in chunks
    ├── Validate each row
    ├── Create/Update products
    ├── Log errors
    └── Generate report

[4] Dispatch ImportCompleted event
```

### 16. مزامنة القنوات الخارجية (Channel Sync)

```http
POST /api/v1/products/{id}/sync
{
  "channels": ["amazon", "ebay"]
}
```

**خطوات التنفيذ:**
```
[1] For each channel:
    │
    [2] Transform product to channel format
    │
    [3] Call channel API
    │   ├── Create if new
    │   └── Update if exists
    │
    [4] Store channel reference
    │   └── INSERT INTO product_channels (product_id, channel, external_id, ...)
    │
    [5] Log sync result

[6] Handle failures:
    ├── Retry with backoff
    └── Alert on persistent failure
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                     Product Lifecycle Flow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Manager          System           Inventory       Customer     │
│    │                │                  │              │          │
│    │── Create ─────▶│                  │              │          │
│    │   + Variants   │                  │              │          │
│    │   + Prices     │                  │              │          │
│    │                │                  │              │          │
│    │── Set Stock ──▶│─── Update ──────▶│              │          │
│    │                │                  │              │          │
│    │── Publish ────▶│                  │              │          │
│    │                │── Sync Channels ─│              │          │
│    │                │── Update Feeds ──│              │          │
│    │                │                  │              │          │
│    │                │                  │    ◀── View ─│          │
│    │                │                  │    ◀── Buy ──│          │
│    │                │                  │              │          │
│    │                │◀── Reserve ──────│              │          │
│    │                │                  │              │          │
│    │                │◀── Confirm ──────│              │          │
│    │                │─── Deduct ──────▶│              │          │
│    │                │                  │              │          │
│    │                │◀── Low Stock ────│              │          │
│    │◀── Alert ──────│                  │              │          │
│    │                │                  │              │          │
│    │── Restock ────▶│─── Update ──────▶│              │          │
│    │                │                  │              │          │
│    │                │◀── Out of Stock ─│              │          │
│    │                │── Notify ────────│─────────────▶│          │
│    │                │   (back in stock)│              │          │
│    │                │                  │              │          │
│    │── Discontinue ▶│                  │              │          │
│    │                │── Remove ────────│              │          │
│    │                │── Redirect ──────│              │          │
│    │                │                  │              │          │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات المنتجات**
