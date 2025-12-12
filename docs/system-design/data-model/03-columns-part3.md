# تحديد الأعمدة لكل كيان (Columns Definition) - الجزء الثالث
## التسعير، العملات، اللغات، المستخدمين، الإعدادات

---

## 12. Testimonials (التوصيات)

```sql
CREATE TABLE testimonials (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Source
    source              VARCHAR(50) NOT NULL DEFAULT 'manual',
                        -- ENUM: manual, form, import, request
    
    -- Control
    status              VARCHAR(20) NOT NULL DEFAULT 'pending',
    
    -- Featured
    is_featured         BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Client Info
    client_name         VARCHAR(255) NOT NULL,
    client_title        VARCHAR(255) NULL,
    client_company      VARCHAR(255) NULL,
    client_photo_id     UUID NULL REFERENCES media(id),
    client_website      VARCHAR(255) NULL,
    
    -- Rating
    rating              DECIMAL(2,1) NULL, -- 1.0 to 5.0
    
    -- Verification
    is_verified         BOOLEAN NOT NULL DEFAULT FALSE,
    verified_at         TIMESTAMP NULL,
    verified_by         UUID NULL REFERENCES users(id),
    verification_method VARCHAR(50) NULL,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    updated_by          UUID NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL,
    published_at        TIMESTAMP NULL
);
```

### testimonial_translations (ترجمات التوصيات)

```sql
CREATE TABLE testimonial_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    testimonial_id      UUID NOT NULL REFERENCES testimonials(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    content             TEXT NOT NULL,
    client_title        VARCHAR(255) NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(testimonial_id, locale)
);
```

---

## 13. Static Blocks (الأقسام الثابتة)

```sql
CREATE TABLE static_blocks (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    identifier          VARCHAR(100) NOT NULL UNIQUE,
    
    -- Type
    type                VARCHAR(50) NOT NULL DEFAULT 'html',
                        -- ENUM: html, banner, cta, hero, features, 
                        --       stats, testimonials, newsletter
    
    -- Control
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
    
    -- Versioning
    version             INTEGER NOT NULL DEFAULT 1,
    
    -- Visibility
    visibility_rules    JSONB NULL,
    show_from           TIMESTAMP NULL,
    show_until          TIMESTAMP NULL,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    updated_by          UUID NULL REFERENCES users(id),
    
    -- Settings
    settings            JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL,
    published_at        TIMESTAMP NULL
);
```

### static_block_translations (ترجمات الأقسام)

```sql
CREATE TABLE static_block_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    block_id            UUID NOT NULL REFERENCES static_blocks(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    title               VARCHAR(255) NULL,
    content             TEXT NULL,
    
    -- Structured Content (for typed blocks)
    structured_content  JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(block_id, locale)
);
```

---

## 14. Pricing Plans (خطط التسعير)

```sql
CREATE TABLE pricing_plans (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    slug                VARCHAR(100) NOT NULL UNIQUE,
    
    -- Type
    type                VARCHAR(50) NOT NULL DEFAULT 'subscription',
                        -- ENUM: subscription, one_time, usage_based
    
    -- Billing
    billing_period      VARCHAR(20) NULL,
                        -- ENUM: monthly, quarterly, yearly, lifetime
    billing_interval    INTEGER NULL DEFAULT 1,
    
    -- Trial
    trial_days          INTEGER NOT NULL DEFAULT 0,
    
    -- Control
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Display
    is_featured         BOOLEAN NOT NULL DEFAULT FALSE,
    is_recommended      BOOLEAN NOT NULL DEFAULT FALSE,
    is_hidden           BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Appearance
    color               VARCHAR(20) NULL,
    icon                VARCHAR(100) NULL,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    updated_by          UUID NULL REFERENCES users(id),
    
    -- Metadata
    meta                JSONB NULL,
    settings            JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL,
    published_at        TIMESTAMP NULL
);
```

### pricing_plan_translations (ترجمات الخطط)

```sql
CREATE TABLE pricing_plan_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    plan_id             UUID NOT NULL REFERENCES pricing_plans(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    name                VARCHAR(255) NOT NULL,
    description         TEXT NULL,
    short_description   TEXT NULL,
    cta_text            VARCHAR(100) NULL,
    badge_text          VARCHAR(50) NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(plan_id, locale)
);
```

### plan_prices (أسعار الخطط)

```sql
CREATE TABLE plan_prices (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    plan_id             UUID NOT NULL REFERENCES pricing_plans(id) ON DELETE CASCADE,
    currency_id         UUID NOT NULL REFERENCES currencies(id),
    
    -- Billing Period
    billing_period      VARCHAR(20) NOT NULL,
    
    -- Pricing
    amount              DECIMAL(15,4) NOT NULL,
    setup_fee           DECIMAL(15,4) NULL DEFAULT 0,
    
    -- Validity
    starts_at           TIMESTAMP NULL,
    ends_at             TIMESTAMP NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(plan_id, currency_id, billing_period),
    CHECK (amount >= 0)
);
```

### plan_features (ميزات الخطط)

```sql
CREATE TABLE plan_features (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    plan_id             UUID NOT NULL REFERENCES pricing_plans(id) ON DELETE CASCADE,
    
    -- Identification
    feature_key         VARCHAR(100) NOT NULL,
    
    -- Type
    type                VARCHAR(20) NOT NULL DEFAULT 'boolean',
                        -- ENUM: boolean, limit, text
    
    -- Value
    value               VARCHAR(255) NOT NULL,
    
    -- Display
    is_highlighted      BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(plan_id, feature_key)
);
```

### plan_feature_translations (ترجمات الميزات)

```sql
CREATE TABLE plan_feature_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    feature_id          UUID NOT NULL REFERENCES plan_features(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    label               VARCHAR(255) NOT NULL,
    tooltip             TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(feature_id, locale)
);
```

### plan_limits (حدود الاستخدام)

```sql
CREATE TABLE plan_limits (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    plan_id             UUID NOT NULL REFERENCES pricing_plans(id) ON DELETE CASCADE,
    
    -- Resource
    resource            VARCHAR(100) NOT NULL, -- api_calls, storage, users
    
    -- Limit
    limit_value         INTEGER NULL, -- NULL = unlimited
    period              VARCHAR(20) NULL, -- monthly, daily, total
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(plan_id, resource)
);
```

### subscriptions (الاشتراكات)

```sql
CREATE TABLE subscriptions (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    user_id             UUID NOT NULL REFERENCES users(id),
    plan_id             UUID NOT NULL REFERENCES pricing_plans(id),
    
    -- Status
    status              VARCHAR(20) NOT NULL DEFAULT 'pending',
                        -- ENUM: pending, trial, active, paused, 
                        --       past_due, cancelled, expired
    
    -- Billing
    billing_period      VARCHAR(20) NOT NULL,
    currency_id         UUID NOT NULL REFERENCES currencies(id),
    
    -- Amount
    amount              DECIMAL(15,4) NOT NULL,
    
    -- Coupon
    coupon_id           UUID NULL REFERENCES coupons(id),
    discount_amount     DECIMAL(15,4) NULL,
    
    -- Period Dates
    current_period_start TIMESTAMP NOT NULL,
    current_period_end   TIMESTAMP NOT NULL,
    
    -- Trial
    trial_ends_at       TIMESTAMP NULL,
    
    -- Cancellation
    cancel_at_period_end BOOLEAN NOT NULL DEFAULT FALSE,
    cancelled_at        TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    
    -- Pause
    paused_at           TIMESTAMP NULL,
    resume_at           TIMESTAMP NULL,
    
    -- Pending Changes
    pending_plan_id     UUID NULL REFERENCES pricing_plans(id),
    pending_effective_at TIMESTAMP NULL,
    
    -- External Gateway
    gateway             VARCHAR(50) NULL, -- stripe, paddle
    gateway_subscription_id VARCHAR(255) NULL,
    gateway_customer_id VARCHAR(255) NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    ends_at             TIMESTAMP NULL
);

CREATE INDEX idx_subscriptions_user ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_plan ON subscriptions(plan_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);
CREATE INDEX idx_subscriptions_period ON subscriptions(current_period_end);
```

### subscription_usages (استخدام الاشتراكات)

```sql
CREATE TABLE subscription_usages (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    subscription_id     UUID NOT NULL REFERENCES subscriptions(id) ON DELETE CASCADE,
    
    -- Resource
    resource            VARCHAR(100) NOT NULL,
    
    -- Usage
    used                INTEGER NOT NULL DEFAULT 0,
    limit_value         INTEGER NULL,
    
    -- Period
    period_start        TIMESTAMP NOT NULL,
    period_end          TIMESTAMP NOT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(subscription_id, resource, period_start)
);
```

### coupons (الكوبونات)

```sql
CREATE TABLE coupons (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    code                VARCHAR(50) NOT NULL UNIQUE,
    
    -- Type
    type                VARCHAR(20) NOT NULL,
                        -- ENUM: percentage, fixed_amount, trial_extension
    
    -- Value
    value               DECIMAL(15,4) NOT NULL,
    
    -- Currency (for fixed_amount)
    currency_id         UUID NULL REFERENCES currencies(id),
    
    -- Limits
    max_uses            INTEGER NULL,
    max_uses_per_user   INTEGER NULL DEFAULT 1,
    used_count          INTEGER NOT NULL DEFAULT 0,
    
    -- Applicability
    applies_to_plans    JSONB NULL, -- null = all plans
    applies_to_periods  JSONB NULL, -- ['monthly', 'yearly']
    
    -- Duration
    duration            VARCHAR(20) NOT NULL DEFAULT 'once',
                        -- ENUM: once, forever, repeating
    duration_months     INTEGER NULL,
    
    -- First Payment Only
    first_payment_only  BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Minimum
    min_amount          DECIMAL(15,4) NULL,
    
    -- Validity
    starts_at           TIMESTAMP NULL,
    expires_at          TIMESTAMP NULL,
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL
);

CREATE INDEX idx_coupons_code ON coupons(code);
CREATE INDEX idx_coupons_active ON coupons(is_active, expires_at);
```

---

## 15. Currencies (العملات)

```sql
CREATE TABLE currencies (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    code                VARCHAR(3) NOT NULL UNIQUE, -- ISO 4217
    numeric_code        VARCHAR(3) NULL,
    
    -- Symbol
    symbol              VARCHAR(10) NOT NULL,
    symbol_native       VARCHAR(10) NULL,
    
    -- Format
    decimal_places      INTEGER NOT NULL DEFAULT 2,
    decimal_separator   VARCHAR(5) NOT NULL DEFAULT '.',
    thousands_separator VARCHAR(5) NOT NULL DEFAULT ',',
    symbol_position     VARCHAR(10) NOT NULL DEFAULT 'before',
                        -- ENUM: before, after
    
    -- Rounding
    rounding_mode       VARCHAR(20) NOT NULL DEFAULT 'half_up',
    rounding_increment  DECIMAL(10,4) NULL,
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    is_default          BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);

CREATE INDEX idx_currencies_code ON currencies(code);
CREATE INDEX idx_currencies_active ON currencies(is_active);
```

### currency_translations (ترجمات العملات)

```sql
CREATE TABLE currency_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    currency_id         UUID NOT NULL REFERENCES currencies(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    name                VARCHAR(100) NOT NULL,
    name_plural         VARCHAR(100) NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(currency_id, locale)
);
```

### exchange_rates (أسعار الصرف)

```sql
CREATE TABLE exchange_rates (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    from_currency_id    UUID NOT NULL REFERENCES currencies(id),
    to_currency_id      UUID NOT NULL REFERENCES currencies(id),
    
    -- Rate
    rate                DECIMAL(20,10) NOT NULL,
    
    -- Source
    source              VARCHAR(50) NOT NULL DEFAULT 'manual',
                        -- ENUM: manual, api, bank
    provider            VARCHAR(50) NULL,
    
    -- Freeze
    is_frozen           BOOLEAN NOT NULL DEFAULT FALSE,
    frozen_at           TIMESTAMP NULL,
    frozen_by           UUID NULL REFERENCES users(id),
    frozen_until        TIMESTAMP NULL,
    
    -- Validity
    effective_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Relations (FK)
    updated_by          UUID NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(from_currency_id, to_currency_id),
    CHECK (from_currency_id != to_currency_id),
    CHECK (rate > 0)
);

CREATE INDEX idx_exchange_rates_currencies ON exchange_rates(from_currency_id, to_currency_id);
```

### exchange_rate_history (تاريخ أسعار الصرف)

```sql
CREATE TABLE exchange_rate_history (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    exchange_rate_id    UUID NOT NULL REFERENCES exchange_rates(id) ON DELETE CASCADE,
    
    -- Rate
    rate                DECIMAL(20,10) NOT NULL,
    
    -- Source
    source              VARCHAR(50) NOT NULL,
    provider            VARCHAR(50) NULL,
    
    -- Timestamp
    recorded_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_rate_history_rate ON exchange_rate_history(exchange_rate_id);
CREATE INDEX idx_rate_history_date ON exchange_rate_history(recorded_at);
```

---

## 16. Languages (اللغات)

```sql
CREATE TABLE languages (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    code                VARCHAR(10) NOT NULL UNIQUE, -- ISO 639-1
    locale              VARCHAR(20) NOT NULL UNIQUE, -- en_US, ar_SA
    
    -- Names
    name                VARCHAR(100) NOT NULL,
    native_name         VARCHAR(100) NOT NULL,
    
    -- Script
    script              VARCHAR(10) NULL, -- Latn, Arab
    
    -- Direction
    direction           VARCHAR(3) NOT NULL DEFAULT 'ltr',
                        -- ENUM: ltr, rtl
    
    -- Display
    flag_icon           VARCHAR(20) NULL,
    
    -- Fallback
    fallback_id         UUID NULL REFERENCES languages(id),
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT FALSE,
    is_default          BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Settings
    date_format         VARCHAR(50) NULL DEFAULT 'YYYY-MM-DD',
    time_format         VARCHAR(50) NULL DEFAULT 'HH:mm',
    number_format       JSONB NULL,
    
    -- Statistics
    translation_progress INTEGER NOT NULL DEFAULT 0, -- percentage
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);

CREATE INDEX idx_languages_code ON languages(code);
CREATE INDEX idx_languages_active ON languages(is_active);
```

### translation_groups (مجموعات الترجمة)

```sql
CREATE TABLE translation_groups (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    name                VARCHAR(100) NOT NULL UNIQUE,
    
    -- Description
    description         TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);
```

### translation_keys (مفاتيح الترجمة)

```sql
CREATE TABLE translation_keys (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    group_id            UUID NULL REFERENCES translation_groups(id),
    
    -- Key
    key                 VARCHAR(255) NOT NULL,
    
    -- Type
    type                VARCHAR(20) NOT NULL DEFAULT 'text',
                        -- ENUM: text, html, json
    
    -- Source
    source              VARCHAR(50) NULL, -- file, database
    
    -- Control
    is_system           BOOLEAN NOT NULL DEFAULT FALSE,
    is_deprecated       BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(group_id, key)
);

CREATE INDEX idx_translation_keys_group ON translation_keys(group_id);
CREATE INDEX idx_translation_keys_key ON translation_keys(key);
```

### translation_values (قيم الترجمة)

```sql
CREATE TABLE translation_values (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    key_id              UUID NOT NULL REFERENCES translation_keys(id) ON DELETE CASCADE,
    language_id         UUID NOT NULL REFERENCES languages(id) ON DELETE CASCADE,
    
    -- Value
    value               TEXT NULL,
    
    -- Status
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
                        -- ENUM: draft, pending_review, approved, 
                        --       machine_translated
    
    -- Source
    is_machine_translated BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Relations (FK)
    translated_by       UUID NULL REFERENCES users(id),
    reviewed_by         UUID NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    reviewed_at         TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(key_id, language_id)
);

CREATE INDEX idx_translation_values_key ON translation_values(key_id);
CREATE INDEX idx_translation_values_lang ON translation_values(language_id);
CREATE INDEX idx_translation_values_status ON translation_values(status);
```

---

## 17. Users (المستخدمون)

```sql
CREATE TABLE users (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Authentication
    email               VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at   TIMESTAMP NULL,
    password            VARCHAR(255) NOT NULL,
    
    -- Profile
    name                VARCHAR(255) NOT NULL,
    avatar_id           UUID NULL REFERENCES media(id),
    
    -- Status
    status              VARCHAR(20) NOT NULL DEFAULT 'active',
                        -- ENUM: pending, active, suspended, banned
    
    -- Security
    two_factor_enabled  BOOLEAN NOT NULL DEFAULT FALSE,
    two_factor_secret   VARCHAR(255) NULL,
    
    -- Password
    password_changed_at TIMESTAMP NULL,
    must_change_password BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Locale
    locale              VARCHAR(10) NULL REFERENCES languages(code),
    timezone            VARCHAR(50) NULL DEFAULT 'UTC',
    
    -- Last Activity
    last_login_at       TIMESTAMP NULL,
    last_login_ip       INET NULL,
    last_active_at      TIMESTAMP NULL,
    
    -- Remember Token
    remember_token      VARCHAR(100) NULL,
    
    -- Metadata
    meta                JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
```

### user_profiles (بيانات المستخدمين)

```sql
CREATE TABLE user_profiles (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    user_id             UUID NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    
    -- Personal
    first_name          VARCHAR(100) NULL,
    last_name           VARCHAR(100) NULL,
    phone               VARCHAR(50) NULL,
    date_of_birth       DATE NULL,
    gender              VARCHAR(20) NULL,
    
    -- Address
    address             TEXT NULL,
    city                VARCHAR(100) NULL,
    state               VARCHAR(100) NULL,
    country             VARCHAR(2) NULL, -- ISO 3166-1 alpha-2
    postal_code         VARCHAR(20) NULL,
    
    -- Bio
    bio                 TEXT NULL,
    website             VARCHAR(255) NULL,
    
    -- Social
    social_links        JSONB NULL,
    
    -- Company
    company             VARCHAR(255) NULL,
    job_title           VARCHAR(255) NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);
```

### roles (الأدوار)

```sql
CREATE TABLE roles (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    slug                VARCHAR(100) NOT NULL UNIQUE,
    name                VARCHAR(100) NOT NULL,
    
    -- Description
    description         TEXT NULL,
    
    -- Type
    is_system           BOOLEAN NOT NULL DEFAULT FALSE,
    is_default          BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Guard
    guard_name          VARCHAR(50) NOT NULL DEFAULT 'web',
    
    -- Relations (FK)
    created_by          UUID NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);
```

### permissions (الصلاحيات)

```sql
CREATE TABLE permissions (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    slug                VARCHAR(100) NOT NULL UNIQUE,
    name                VARCHAR(100) NOT NULL,
    
    -- Grouping
    group_name          VARCHAR(100) NULL,
    
    -- Description
    description         TEXT NULL,
    
    -- Guard
    guard_name          VARCHAR(50) NOT NULL DEFAULT 'web',
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);
```

### role_permissions (ربط الأدوار بالصلاحيات)

```sql
CREATE TABLE role_permissions (
    -- Relations
    role_id             UUID NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    permission_id       UUID NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    
    -- Primary
    PRIMARY KEY (role_id, permission_id)
);
```

### user_roles (ربط المستخدمين بالأدوار)

```sql
CREATE TABLE user_roles (
    -- Relations
    user_id             UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role_id             UUID NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    
    -- Primary
    PRIMARY KEY (user_id, role_id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

---

## 18. Settings (الإعدادات)

```sql
CREATE TABLE setting_groups (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    slug                VARCHAR(100) NOT NULL UNIQUE,
    name                VARCHAR(100) NOT NULL,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);
```

```sql
CREATE TABLE settings (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    group_id            UUID NULL REFERENCES setting_groups(id),
    
    -- Identification
    key                 VARCHAR(255) NOT NULL UNIQUE,
    
    -- Value
    value               TEXT NULL,
    
    -- Type
    type                VARCHAR(20) NOT NULL DEFAULT 'string',
                        -- ENUM: string, integer, boolean, json, 
                        --       array, file, color
    
    -- Validation
    validation_rules    JSONB NULL,
    options             JSONB NULL, -- for select type
    
    -- Default
    default_value       TEXT NULL,
    
    -- Control
    is_public           BOOLEAN NOT NULL DEFAULT FALSE,
    is_encrypted        BOOLEAN NOT NULL DEFAULT FALSE,
    is_system           BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Autoload
    autoload            BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);

CREATE INDEX idx_settings_key ON settings(key);
CREATE INDEX idx_settings_autoload ON settings(autoload) WHERE autoload = TRUE;
```

### user_settings (إعدادات المستخدم)

```sql
CREATE TABLE user_settings (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    user_id             UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    
    -- Setting
    key                 VARCHAR(255) NOT NULL,
    value               TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(user_id, key)
);
```

---

**نهاية الجزء الثالث - يتبع في الجزء الرابع (ERD)**
