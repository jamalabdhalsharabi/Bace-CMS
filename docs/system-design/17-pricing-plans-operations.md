# تحليل عمليات الأسعار والخطط (Pricing / Plans / Packages Operations)

## 📋 نظرة عامة
الأسعار والخطط هي كيانات تجارية تدير التسعير المتدرج، الاشتراكات، والباقات. تدعم التعدد اللغوي، متعدد العملات، والفترات المختلفة.

---

## 🔄 State Machine Diagram - الخطة

```
┌──────────┐   create   ┌──────────┐
│  (new)   │───────────▶│  draft   │
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
       │ inactive  │   │ scheduled │   │ soft_deleted │
       └───────────┘   └───────────┘   └──────────────┘
              │               │
              └───────┬───────┘
                      ▼
               ┌───────────┐
               │  active   │
               └───────────┘
```

## 🔄 State Machine Diagram - الاشتراك

```
┌──────────┐   create   ┌─────────────┐
│  (new)   │───────────▶│   pending   │
└──────────┘            └─────────────┘
                              │
                    ┌─────────┴─────────┐
                    ▼                   ▼
             ┌───────────┐       ┌───────────┐
             │   trial   │       │  active   │
             └───────────┘       └───────────┘
                    │                   │
                    └─────────┬─────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
       ┌───────────┐   ┌───────────┐   ┌───────────┐
       │  paused   │   │  expired  │   │ cancelled │
       └───────────┘   └───────────┘   └───────────┘
                              │
                              ▼
                       ┌───────────┐
                       │  renewed  │
                       └───────────┘
```

---

## 📌 العملية 1: إنشاء خطة تسعير (Create Pricing Plan)

### 1. اسم العملية
`pricing_plan.create`

### 2. الهدف
إنشاء خطة تسعير جديدة مع الميزات والأسعار.

### 3. الشروط المسبقة
- صلاحية `pricing_plan.create`
- الاسم والـ slug فريدان
- الأسعار صالحة

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate slug uniqueness
    ├── Validate prices per currency
    ├── Validate billing periods
    └── Validate features structure

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Generate UUID
    │
    [4] Create Plan Record
    │   └── INSERT INTO pricing_plans (id, slug, type, billing_period, trial_days, status, ...)
    │
    [5] Create Translation Records
    │   └── INSERT INTO pricing_plan_translations (plan_id, locale, name, description, ...)
    │
    [6] Create Price Records
    │   └── INSERT INTO plan_prices (plan_id, currency_id, amount, billing_period, ...)
    │
    [7] Create Features
    │   └── INSERT INTO plan_features (plan_id, feature_key, value, is_highlighted, order, ...)
    │
    [8] Create Feature Translations
    │   └── INSERT INTO plan_feature_translations (feature_id, locale, label, tooltip, ...)
    │
    [9] Set Usage Limits (if applicable)
    │   └── INSERT INTO plan_limits (plan_id, resource, limit_value, period, ...)
    │
COMMIT TRANSACTION ───────────────────────────────────

[10] Dispatch Events
     └── PricingPlanCreated event

[11] Queue Jobs
     ├── InvalidatePricingCacheJob
     └── SyncToPaymentGatewayJob
```

### 5. الآثار الجانبية
- إنشاء سجل الخطة
- إنشاء الأسعار لكل عملة
- إنشاء الميزات
- مزامنة مع بوابة الدفع

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Duplicate Slug | Return 422 + suggestion |
| Invalid Price | Return 422 + details |
| Gateway Sync Failed | Log, retry later |

### 7. Security Considerations
- صلاحية خاصة لإنشاء الخطط
- التحقق من صلاحية الأسعار
- تسجيل جميع التغييرات

### 8. Roles & Permissions
| الدور | الصلاحية |
|------|---------|
| Super Admin | ✅ |
| Admin | ✅ |
| Sales Manager | ✅ |
| Others | ❌ |

### 9. API Endpoint

```http
POST /api/v1/pricing-plans
Authorization: Bearer {token}
Content-Type: application/json

{
  "slug": "professional",
  "type": "subscription",
  "billing_periods": ["monthly", "yearly"],
  "trial_days": 14,
  "translations": {
    "ar": { "name": "الخطة الاحترافية", "description": "..." },
    "en": { "name": "Professional Plan", "description": "..." }
  },
  "prices": {
    "monthly": [
      { "currency": "USD", "amount": 49.99 },
      { "currency": "SAR", "amount": 187.50 }
    ],
    "yearly": [
      { "currency": "USD", "amount": 499.99 },
      { "currency": "SAR", "amount": 1875.00 }
    ]
  },
  "features": [
    {
      "key": "users",
      "value": "10",
      "type": "limit",
      "translations": { "ar": { "label": "عدد المستخدمين" } }
    },
    {
      "key": "storage",
      "value": "50GB",
      "type": "limit",
      "is_highlighted": true
    },
    {
      "key": "priority_support",
      "value": true,
      "type": "boolean"
    }
  ],
  "limits": {
    "api_calls": { "limit": 10000, "period": "monthly" },
    "storage_gb": { "limit": 50 }
  },
  "is_recommended": true,
  "sort_order": 2
}
```

### 10. Webhook Payload

```json
{
  "event": "pricing_plan.created",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "id": "uuid",
    "slug": "professional",
    "type": "subscription",
    "billing_periods": ["monthly", "yearly"],
    "trial_days": 14
  }
}
```

---

## 📌 العملية 2: تعديل خطة (Update Plan)

### 1. اسم العملية
`pricing_plan.update`

### 2. خطوات التنفيذ

```
[1] Load plan with lock

[2] Check for active subscriptions
    └── Warn if price change affects subscribers

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Update Plan Record
    │
    [5] Sync Translations
    │
    [6] Sync Prices
    │   └── Archive old prices if changed
    │
    [7] Sync Features
    │
COMMIT ───────────────────────────────────────────────

[8] Queue Jobs
    ├── InvalidatePricingCacheJob
    ├── NotifyAffectedSubscribersJob (if significant change)
    └── SyncToPaymentGatewayJob
```

### 3. Implementation Notes
- تغيير السعر لا يؤثر على الاشتراكات الحالية (إلا بالتجديد)
- تغيير الميزات قد يؤثر فوراً

---

## 📌 العملية 3: حذف خطة (Delete Plan)

```http
DELETE /api/v1/pricing-plans/{id}
{
  "migrate_subscribers_to": "plan-uuid",
  "force": false
}
```

**خطوات التنفيذ:**
```
[1] Check for active subscriptions

[2] If has active subscriptions and no migration:
    └── Return error

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Migrate subscribers (if specified)
    │
    [5] Soft delete plan
    │
COMMIT ───────────────────────────────────────────────

[6] Queue SyncToPaymentGatewayJob (archive plan)
```

---

## 📌 العملية 4: تفعيل/تعطيل خطة (Enable/Disable Plan)

```http
POST /api/v1/pricing-plans/{id}/enable
POST /api/v1/pricing-plans/{id}/disable
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status
    │
    [3] If disabling:
    │   └── Hide from public pricing page
    │   └── Allow existing subscribers to continue
    │
COMMIT ───────────────────────────────────────────────

[4] Queue InvalidatePricingCacheJob
```

---

## 📌 العملية 5: تعيين كخطة افتراضية (Set as Default)

```http
POST /api/v1/pricing-plans/{id}/set-default
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Remove default from current
    │
    [3] Set new default
    │
COMMIT ───────────────────────────────────────────────

[4] Dispatch PlanSetAsDefault event
```

---

## 📌 العملية 6: تعيين كخطة موصى بها (Set as Recommended)

```http
POST /api/v1/pricing-plans/{id}/set-recommended
```

---

## 📌 العملية 7: إنشاء نسخة مترجمة (Create Translation)

*نفس نمط أنواع المحتوى الأخرى*

---

## 📌 العملية 8: إضافة ميزة للخطة (Add Feature)

```http
POST /api/v1/pricing-plans/{id}/features
{
  "key": "custom_domain",
  "value": true,
  "type": "boolean",
  "is_highlighted": true,
  "translations": {
    "ar": { "label": "دومين مخصص", "tooltip": "ربط دومين خاص بك" }
  }
}
```

---

## 📌 العملية 9: إزالة ميزة (Remove Feature)

```http
DELETE /api/v1/pricing-plans/{id}/features/{feature_id}
```

---

## 📌 العملية 10: تعديل ميزة (Update Feature)

```http
PUT /api/v1/pricing-plans/{id}/features/{feature_id}
{
  "value": "100GB",
  "is_highlighted": true
}
```

---

## 📌 العملية 11: إعادة ترتيب الميزات (Reorder Features)

```http
PUT /api/v1/pricing-plans/{id}/features/reorder
{
  "order": ["feature-uuid-1", "feature-uuid-2", ...]
}
```

---

## 📌 العملية 12: تعيين السعر (Set Price)

```http
PUT /api/v1/pricing-plans/{id}/prices
{
  "billing_period": "monthly",
  "currency": "USD",
  "amount": 59.99,
  "effective_from": "2024-02-01"
}
```

**خطوات التنفيذ:**
```
[1] Validate price > 0

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Archive current price
    │
    [4] Create or schedule new price
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs
    ├── SyncToPaymentGatewayJob
    └── NotifyAffectedSubscribersJob (renewal price change)
```

---

## 📌 العملية 13: جدولة تغيير السعر (Schedule Price Change)

```http
POST /api/v1/pricing-plans/{id}/schedule-price
{
  "billing_period": "monthly",
  "new_prices": [
    { "currency": "USD", "amount": 69.99 }
  ],
  "effective_from": "2024-03-01",
  "notify_subscribers": true
}
```

---

## 📌 العملية 14: تطبيق خصم (Apply Discount)

```http
POST /api/v1/pricing-plans/{id}/discount
{
  "type": "percentage",
  "value": 20,
  "billing_periods": ["yearly"],
  "starts_at": "2024-01-20",
  "ends_at": "2024-01-31"
}
```

---

## 📌 العملية 15: إنشاء كوبون (Create Coupon)

```http
POST /api/v1/coupons
{
  "code": "SAVE20",
  "type": "percentage",
  "value": 20,
  "applies_to": {
    "plans": ["plan-uuid-1", "plan-uuid-2"],
    "billing_periods": ["yearly"]
  },
  "usage_limit": 100,
  "per_user_limit": 1,
  "starts_at": "2024-01-01",
  "expires_at": "2024-12-31",
  "first_payment_only": true
}
```

---

## 📌 العملية 16: تعطيل كوبون (Disable Coupon)

```http
POST /api/v1/coupons/{code}/disable
```

---

## 📌 العملية 17: ربط بمنتج/خدمة (Link to Product/Service)

```http
POST /api/v1/pricing-plans/{id}/link
{
  "entity_type": "service",
  "entity_id": "service-uuid"
}
```

---

## 📌 العملية 18: إعادة ترتيب الخطط (Reorder Plans)

```http
PUT /api/v1/pricing-plans/reorder
{
  "order": ["basic-uuid", "pro-uuid", "enterprise-uuid"]
}
```

---

## 📌 العملية 19: استنساخ خطة (Clone Plan)

```http
POST /api/v1/pricing-plans/{id}/clone
{
  "new_slug": "professional-v2",
  "include_prices": true,
  "include_features": true
}
```

---

## 📌 العملية 20: مقارنة الخطط (Compare Plans)

```http
GET /api/v1/pricing-plans/compare?plans=basic,pro,enterprise
```

**Response:**
```json
{
  "plans": [...],
  "features_matrix": {
    "users": { "basic": "5", "pro": "10", "enterprise": "Unlimited" },
    "storage": { "basic": "10GB", "pro": "50GB", "enterprise": "500GB" }
  }
}
```

---

## 📌 العملية 21: تعيين حدود الاستخدام (Set Usage Limits)

```http
PUT /api/v1/pricing-plans/{id}/limits
{
  "limits": {
    "api_calls": { "limit": 10000, "period": "monthly" },
    "storage_gb": { "limit": 50 },
    "team_members": { "limit": 10 }
  }
}
```

---

## 📌 العملية 22: تعيين فترة التجربة (Set Trial Period)

```http
PUT /api/v1/pricing-plans/{id}/trial
{
  "trial_days": 14,
  "require_payment_method": false,
  "features_during_trial": "full"
}
```

---

## 📌 عمليات الاشتراك (Subscription Operations)

### 23. إنشاء اشتراك جديد (Create Subscription)

```http
POST /api/v1/subscriptions
{
  "user_id": "user-uuid",
  "plan_id": "plan-uuid",
  "billing_period": "monthly",
  "payment_method_id": "pm-uuid",
  "coupon_code": "SAVE20",
  "start_immediately": true
}
```

**خطوات التنفيذ:**
```
[1] Validate plan available

[2] Validate coupon (if provided)

[3] Calculate first payment
    ├── Apply trial if eligible
    ├── Apply coupon discount
    └── Prorate if upgrading

[4] BEGIN TRANSACTION ────────────────────────────────
    │
    [5] Create Subscription Record
    │   └── INSERT INTO subscriptions (id, user_id, plan_id, status, ...)
    │
    [6] If not trial:
    │   └── Process payment
    │
    [7] Create usage tracking record
    │
    [8] Grant plan features/permissions
    │
COMMIT ───────────────────────────────────────────────

[9] Dispatch Events
    └── SubscriptionCreated event

[10] Queue Jobs
     ├── SendWelcomeEmailJob
     ├── ProvisionResourcesJob
     └── SyncToAnalyticsJob
```

---

### 24. ترقية اشتراك (Upgrade Subscription)

```http
POST /api/v1/subscriptions/{id}/upgrade
{
  "new_plan_id": "higher-plan-uuid",
  "prorate": true,
  "effective": "immediately"
}
```

**خطوات التنفيذ:**
```
[1] Validate new plan is higher tier

[2] Calculate proration
    └── Credit remaining days, charge difference

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Process prorated payment
    │
    [5] Update subscription plan
    │
    [6] Update usage limits
    │
    [7] Grant new features
    │
COMMIT ───────────────────────────────────────────────

[8] Queue Jobs
    ├── SendUpgradeConfirmationJob
    └── ProvisionAdditionalResourcesJob
```

---

### 25. تخفيض اشتراك (Downgrade Subscription)

```http
POST /api/v1/subscriptions/{id}/downgrade
{
  "new_plan_id": "lower-plan-uuid",
  "effective": "end_of_period"
}
```

**خطوات التنفيذ:**
```
[1] Validate downgrade allowed

[2] Check usage against new limits
    └── Warn if over limit

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Schedule downgrade at period end
    │
    [5] Set pending_plan_id
    │
COMMIT ───────────────────────────────────────────────

[6] Queue SendDowngradeScheduledEmailJob
```

---

### 26. إلغاء اشتراك (Cancel Subscription)

```http
POST /api/v1/subscriptions/{id}/cancel
{
  "reason": "too_expensive",
  "feedback": "...",
  "effective": "end_of_period"
}
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Set cancel_at_period_end = true
    │
    [3] Store cancellation reason
    │
    [4] If immediate:
    │   └── Process refund (prorated)
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs
    ├── SendCancellationConfirmationJob
    ├── ScheduleAccessRevocationJob
    └── TriggerRetentionFlowJob
```

---

### 27. إيقاف اشتراك مؤقتاً (Pause Subscription)

```http
POST /api/v1/subscriptions/{id}/pause
{
  "pause_until": "2024-03-01",
  "reason": "vacation"
}
```

**خطوات التنفيذ:**
```
[1] Validate pause allowed by plan

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → paused
    │
    [4] Set paused_at, resume_at
    │
    [5] Stop billing
    │
    [6] Optionally limit features
    │
COMMIT ───────────────────────────────────────────────

[7] Schedule ResumeSubscriptionJob
```

---

### 28. استئناف اشتراك (Resume Subscription)

```http
POST /api/v1/subscriptions/{id}/resume
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status → active
    │
    [3] Clear paused fields
    │
    [4] Restart billing cycle
    │
    [5] Restore full features
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs
    ├── ProcessReactivationPaymentJob
    └── SendResumeConfirmationJob
```

---

### 29. استرداد المبلغ (Refund)

```http
POST /api/v1/subscriptions/{id}/refund
{
  "type": "full",
  "reason": "service_issue",
  "amount": null
}
```

**خطوات التنفيذ:**
```
[1] Validate refund policy

[2] Calculate refund amount
    ├── Full: entire last payment
    └── Prorated: unused days

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Process refund via payment gateway
    │
    [5] Create refund record
    │
    [6] Update subscription status
    │
COMMIT ───────────────────────────────────────────────

[7] Queue SendRefundConfirmationJob
```

---

### 30. تمديد الاشتراك (Extend Subscription)

```http
POST /api/v1/subscriptions/{id}/extend
{
  "days": 30,
  "reason": "compensation",
  "notify_user": true
}
```

---

### 31. تجديد الاشتراك (Renew Subscription)

```
[Automatic Process - Scheduled Job]

[1] Find subscriptions expiring today

[2] For each subscription:
    │
    [3] Attempt payment
    │
    [4] If successful:
    │   ├── Extend period
    │   ├── Create invoice
    │   └── Send receipt
    │
    [5] If failed:
    │   ├── Retry (3 attempts over 7 days)
    │   ├── Send payment failed notification
    │   └── If all retries fail → suspend
```

---

## 📌 العملية 32: عرض إحصائيات الخطة (Plan Analytics)

```http
GET /api/v1/pricing-plans/{id}/analytics
```

**Response:**
```json
{
  "plan_id": "uuid",
  "stats": {
    "total_subscribers": 150,
    "active_subscribers": 145,
    "churned_last_30_days": 5,
    "mrr": 7497.50,
    "arr": 89970.00,
    "average_lifetime_months": 8.5,
    "conversion_rate": 12.5,
    "upgrade_rate": 8.2,
    "downgrade_rate": 2.1
  }
}
```

---

## 📌 العملية 33: تصدير الخطط (Export Plans)

```http
POST /api/v1/pricing-plans/export
{
  "format": "json",
  "include_features": true,
  "include_prices": true
}
```

---

## 📌 العملية 34: استيراد الخطط (Import Plans)

```http
POST /api/v1/pricing-plans/import
Content-Type: multipart/form-data

file: plans.json
mode: merge
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                  Pricing Plan Lifecycle Flow                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Admin          System          Gateway         User            │
│    │              │                │              │              │
│    │── Create ───▶│                │              │              │
│    │   Plan       │── Sync ───────▶│              │              │
│    │              │                │              │              │
│    │── Add ──────▶│                │              │              │
│    │   Features   │                │              │              │
│    │              │                │              │              │
│    │── Set ──────▶│                │              │              │
│    │   Prices     │── Sync ───────▶│              │              │
│    │              │                │              │              │
│    │── Activate ─▶│                │              │              │
│    │              │── Publish ─────│              │              │
│    │              │                │              │              │
│    │              │                │     ◀── View ─│              │
│    │              │                │     ◀─Subscribe│              │
│    │              │◀── Payment ────│◀─────────────│              │
│    │              │                │              │              │
│    │              │── Grant Access─────────────────▶│              │
│    │              │                │              │              │
│    │              │                │     ◀─Upgrade─│              │
│    │              │── Prorate ─────│              │              │
│    │              │── Charge ──────▶│              │              │
│    │              │                │              │              │
│    │              │                │     ◀─Cancel ─│              │
│    │              │── Schedule ────│              │              │
│    │              │   Revocation   │              │              │
│    │              │                │              │              │
│  Scheduler       │                │              │              │
│    │── Renew ────▶│                │              │              │
│    │              │── Charge ──────▶│              │              │
│    │              │── Extend ──────────────────────▶│              │
│    │              │                │              │              │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات الأسعار والخطط**
