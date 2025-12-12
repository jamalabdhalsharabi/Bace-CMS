# تحديد الأعمدة لكل كيان (Columns Definition) - الجزء الأول
## كيانات المحتوى الأساسية والتصنيفات

---

## 🎨 الأنماط المشتركة (Common Patterns)

### نمط المعرفات
```sql
id              UUID PRIMARY KEY DEFAULT uuid_generate_v7()
```

### نمط Timestamps
```sql
created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
updated_at      TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
deleted_at      TIMESTAMP NULL  -- Soft Delete
```

### نمط المستخدم المسؤول
```sql
created_by      UUID NOT NULL REFERENCES users(id)
updated_by      UUID NULL REFERENCES users(id)
deleted_by      UUID NULL REFERENCES users(id)
```

### نمط الحالة
```sql
status          VARCHAR(20) NOT NULL DEFAULT 'draft'
                -- ENUM: draft, pending_review, in_review, approved, 
                --       rejected, published, unpublished, archived, scheduled
```

### نمط الترتيب والعرض
```sql
sort_order      INTEGER NOT NULL DEFAULT 0
is_featured     BOOLEAN NOT NULL DEFAULT FALSE
is_active       BOOLEAN NOT NULL DEFAULT TRUE
```

---

## 1. Services (الخدمات)

```sql
CREATE TABLE services (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Control
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
    is_featured         BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Scheduling
    published_at        TIMESTAMP NULL,
    scheduled_at        TIMESTAMP NULL,
    
    -- Hierarchy & Order
    parent_id           UUID NULL REFERENCES services(id),
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- SEO Helper
    slug                VARCHAR(255) NOT NULL,
    
    -- Versioning
    version             INTEGER NOT NULL DEFAULT 1,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    updated_by          UUID NULL REFERENCES users(id),
    deleted_by          UUID NULL REFERENCES users(id),
    approved_by         UUID NULL REFERENCES users(id),
    
    -- Metadata
    meta                JSONB NULL,
    settings            JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL,
    approved_at         TIMESTAMP NULL,
    
    -- Indexes
    UNIQUE(slug, deleted_at)
);

CREATE INDEX idx_services_status ON services(status);
CREATE INDEX idx_services_featured ON services(is_featured) WHERE is_featured = TRUE;
CREATE INDEX idx_services_published ON services(published_at) WHERE status = 'published';
```

### service_translations (ترجمات الخدمات)

```sql
CREATE TABLE service_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    service_id          UUID NOT NULL REFERENCES services(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable Content
    title               VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL,
    excerpt             TEXT NULL,
    description         TEXT NULL,
    content             TEXT NULL,
    
    -- SEO
    meta_title          VARCHAR(255) NULL,
    meta_description    TEXT NULL,
    meta_keywords       VARCHAR(500) NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(service_id, locale),
    UNIQUE(slug, locale)
);

CREATE INDEX idx_service_trans_locale ON service_translations(locale);
```

---

## 2. Pages (الصفحات)

```sql
CREATE TABLE pages (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Control
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
    is_homepage         BOOLEAN NOT NULL DEFAULT FALSE,
    is_system           BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Template
    template            VARCHAR(100) NOT NULL DEFAULT 'default',
    
    -- Hierarchy
    parent_id           UUID NULL REFERENCES pages(id),
    depth               INTEGER NOT NULL DEFAULT 0,
    path                VARCHAR(1000) NULL, -- Materialized path
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Scheduling
    published_at        TIMESTAMP NULL,
    scheduled_at        TIMESTAMP NULL,
    
    -- Versioning
    version             INTEGER NOT NULL DEFAULT 1,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    updated_by          UUID NULL REFERENCES users(id),
    deleted_by          UUID NULL REFERENCES users(id),
    
    -- Content Structure
    sections            JSONB NULL, -- Page builder sections
    
    -- Metadata
    meta                JSONB NULL,
    settings            JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL,
    
    -- Constraints
    CHECK (parent_id != id)
);

CREATE INDEX idx_pages_parent ON pages(parent_id);
CREATE INDEX idx_pages_path ON pages(path);
CREATE INDEX idx_pages_template ON pages(template);
```

### page_translations (ترجمات الصفحات)

```sql
CREATE TABLE page_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    page_id             UUID NOT NULL REFERENCES pages(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable Content
    title               VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL,
    content             TEXT NULL,
    excerpt             TEXT NULL,
    
    -- SEO
    meta_title          VARCHAR(255) NULL,
    meta_description    TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(page_id, locale),
    UNIQUE(slug, locale)
);
```

---

## 3. Articles (المقالات)

```sql
CREATE TABLE articles (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Type
    type                VARCHAR(50) NOT NULL DEFAULT 'post',
                        -- ENUM: post, news, tutorial, review
    
    -- Control
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
    is_featured         BOOLEAN NOT NULL DEFAULT FALSE,
    is_pinned           BOOLEAN NOT NULL DEFAULT FALSE,
    pin_position        VARCHAR(50) NULL,
    pin_expires_at      TIMESTAMP NULL,
    
    -- Comments
    allow_comments      BOOLEAN NOT NULL DEFAULT TRUE,
    comments_closed_at  TIMESTAMP NULL,
    
    -- Statistics
    view_count          INTEGER NOT NULL DEFAULT 0,
    comment_count       INTEGER NOT NULL DEFAULT 0,
    
    -- Reading
    reading_time        INTEGER NULL, -- minutes
    word_count          INTEGER NULL,
    
    -- Scheduling
    published_at        TIMESTAMP NULL,
    scheduled_at        TIMESTAMP NULL,
    
    -- Versioning
    version             INTEGER NOT NULL DEFAULT 1,
    
    -- Relations (FK)
    author_id           UUID NOT NULL REFERENCES users(id),
    created_by          UUID NOT NULL REFERENCES users(id),
    updated_by          UUID NULL REFERENCES users(id),
    deleted_by          UUID NULL REFERENCES users(id),
    
    -- Featured Image
    featured_image_id   UUID NULL REFERENCES media(id),
    
    -- Metadata
    meta                JSONB NULL,
    settings            JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL
);

CREATE INDEX idx_articles_type ON articles(type);
CREATE INDEX idx_articles_author ON articles(author_id);
CREATE INDEX idx_articles_published ON articles(published_at DESC) WHERE status = 'published';
CREATE INDEX idx_articles_pinned ON articles(is_pinned, pin_position) WHERE is_pinned = TRUE;
```

### article_translations (ترجمات المقالات)

```sql
CREATE TABLE article_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    article_id          UUID NOT NULL REFERENCES articles(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable Content
    title               VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL,
    excerpt             TEXT NULL,
    content             TEXT NULL,
    
    -- SEO
    meta_title          VARCHAR(255) NULL,
    meta_description    TEXT NULL,
    meta_keywords       VARCHAR(500) NULL,
    
    -- Social
    social_title        VARCHAR(255) NULL,
    social_description  TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(article_id, locale),
    UNIQUE(slug, locale)
);
```

---

## 4. Products (المنتجات)

```sql
CREATE TABLE products (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    sku                 VARCHAR(100) NOT NULL,
    barcode             VARCHAR(100) NULL,
    
    -- Type
    type                VARCHAR(50) NOT NULL DEFAULT 'physical',
                        -- ENUM: physical, digital, virtual, subscription
    
    -- Control
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
    is_featured         BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Visibility
    visibility          VARCHAR(20) NOT NULL DEFAULT 'visible',
                        -- ENUM: visible, hidden, catalog_only, search_only
    
    -- Stock
    track_inventory     BOOLEAN NOT NULL DEFAULT TRUE,
    allow_backorder     BOOLEAN NOT NULL DEFAULT FALSE,
    stock_status        VARCHAR(20) NOT NULL DEFAULT 'in_stock',
                        -- ENUM: in_stock, out_of_stock, on_backorder
    
    -- Shipping
    requires_shipping   BOOLEAN NOT NULL DEFAULT TRUE,
    weight              DECIMAL(10,3) NULL,
    weight_unit         VARCHAR(10) NULL DEFAULT 'kg',
    
    -- Tax
    tax_class           VARCHAR(50) NULL,
    
    -- Variants
    has_variants        BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Scheduling
    published_at        TIMESTAMP NULL,
    scheduled_at        TIMESTAMP NULL,
    
    -- Versioning
    version             INTEGER NOT NULL DEFAULT 1,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    updated_by          UUID NULL REFERENCES users(id),
    deleted_by          UUID NULL REFERENCES users(id),
    
    -- Metadata
    meta                JSONB NULL,
    settings            JSONB NULL,
    dimensions          JSONB NULL, -- {length, width, height, unit}
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(sku)
);

CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_type ON products(type);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_stock ON products(stock_status);
```

### product_translations (ترجمات المنتجات)

```sql
CREATE TABLE product_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    product_id          UUID NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable Content
    name                VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL,
    short_description   TEXT NULL,
    description         TEXT NULL,
    
    -- SEO
    meta_title          VARCHAR(255) NULL,
    meta_description    TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(product_id, locale),
    UNIQUE(slug, locale)
);
```

### product_variants (متغيرات المنتجات)

```sql
CREATE TABLE product_variants (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    product_id          UUID NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    
    -- Identification
    sku                 VARCHAR(100) NOT NULL,
    barcode             VARCHAR(100) NULL,
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    is_default          BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Stock
    stock_status        VARCHAR(20) NOT NULL DEFAULT 'in_stock',
    
    -- Shipping
    weight              DECIMAL(10,3) NULL,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Options
    options             JSONB NOT NULL, -- {color: 'red', size: 'M'}
    
    -- Metadata
    meta                JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(sku)
);

CREATE INDEX idx_variants_product ON product_variants(product_id);
```

### product_prices (أسعار المنتجات)

```sql
CREATE TABLE product_prices (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    product_id          UUID NULL REFERENCES products(id) ON DELETE CASCADE,
    variant_id          UUID NULL REFERENCES product_variants(id) ON DELETE CASCADE,
    currency_id         UUID NOT NULL REFERENCES currencies(id),
    
    -- Pricing
    amount              DECIMAL(15,4) NOT NULL,
    compare_at_amount   DECIMAL(15,4) NULL, -- Original price (for sales)
    cost_amount         DECIMAL(15,4) NULL, -- Cost price
    
    -- Validity
    starts_at           TIMESTAMP NULL,
    ends_at             TIMESTAMP NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    CHECK (product_id IS NOT NULL OR variant_id IS NOT NULL),
    CHECK (amount >= 0)
);

CREATE INDEX idx_product_prices_product ON product_prices(product_id);
CREATE INDEX idx_product_prices_variant ON product_prices(variant_id);
CREATE INDEX idx_product_prices_currency ON product_prices(currency_id);
```

### product_inventories (مخزون المنتجات)

```sql
CREATE TABLE product_inventories (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    product_id          UUID NULL REFERENCES products(id) ON DELETE CASCADE,
    variant_id          UUID NULL REFERENCES product_variants(id) ON DELETE CASCADE,
    
    -- Stock Levels
    quantity            INTEGER NOT NULL DEFAULT 0,
    reserved_quantity   INTEGER NOT NULL DEFAULT 0,
    
    -- Thresholds
    low_stock_threshold INTEGER NULL DEFAULT 10,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    CHECK (product_id IS NOT NULL OR variant_id IS NOT NULL),
    CHECK (quantity >= 0),
    CHECK (reserved_quantity >= 0)
);
```

### inventory_movements (حركات المخزون)

```sql
CREATE TABLE inventory_movements (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    inventory_id        UUID NOT NULL REFERENCES product_inventories(id),
    
    -- Movement Details
    type                VARCHAR(50) NOT NULL,
                        -- ENUM: sale, return, adjustment, restock, 
                        --       reservation, reservation_release
    quantity            INTEGER NOT NULL, -- Can be negative
    quantity_before     INTEGER NOT NULL,
    quantity_after      INTEGER NOT NULL,
    
    -- Reference
    reference_type      VARCHAR(50) NULL, -- order, adjustment, transfer
    reference_id        UUID NULL,
    
    -- Details
    reason              VARCHAR(255) NULL,
    notes               TEXT NULL,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_movements_inventory ON inventory_movements(inventory_id);
CREATE INDEX idx_movements_type ON inventory_movements(type);
CREATE INDEX idx_movements_date ON inventory_movements(created_at);
```

---

## 5. Projects (المشاريع)

```sql
CREATE TABLE projects (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Control
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
    is_featured         BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Client
    client_name         VARCHAR(255) NULL,
    client_logo_id      UUID NULL REFERENCES media(id),
    client_website      VARCHAR(255) NULL,
    client_permission   BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Project Details
    project_type        VARCHAR(50) NULL,
    start_date          DATE NULL,
    end_date            DATE NULL,
    
    -- Metrics
    metrics             JSONB NULL, -- {performance: '40%', satisfaction: '95%'}
    
    -- Scheduling
    published_at        TIMESTAMP NULL,
    scheduled_at        TIMESTAMP NULL,
    
    -- Versioning
    version             INTEGER NOT NULL DEFAULT 1,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    updated_by          UUID NULL REFERENCES users(id),
    deleted_by          UUID NULL REFERENCES users(id),
    
    -- Metadata
    meta                JSONB NULL,
    settings            JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL
);
```

### project_translations (ترجمات المشاريع)

```sql
CREATE TABLE project_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    project_id          UUID NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable Content
    title               VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL,
    excerpt             TEXT NULL,
    description         TEXT NULL,
    
    -- Case Study
    challenge           TEXT NULL,
    solution            TEXT NULL,
    results             TEXT NULL,
    
    -- SEO
    meta_title          VARCHAR(255) NULL,
    meta_description    TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(project_id, locale),
    UNIQUE(slug, locale)
);
```

---

## 6. Taxonomies (التصنيفات)

### taxonomy_types (أنواع التصنيفات)

```sql
CREATE TABLE taxonomy_types (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    slug                VARCHAR(100) NOT NULL UNIQUE,
    
    -- Configuration
    is_hierarchical     BOOLEAN NOT NULL DEFAULT TRUE,
    is_multiple         BOOLEAN NOT NULL DEFAULT TRUE,
    max_depth           INTEGER NULL,
    
    -- Applies To
    applies_to          JSONB NOT NULL, -- ['service', 'article', 'product']
    
    -- Control
    is_system           BOOLEAN NOT NULL DEFAULT FALSE,
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);
```

### taxonomy_type_translations

```sql
CREATE TABLE taxonomy_type_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    type_id             UUID NOT NULL REFERENCES taxonomy_types(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    name                VARCHAR(100) NOT NULL,
    name_singular       VARCHAR(100) NULL,
    description         TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(type_id, locale)
);
```

### taxonomies (التصنيفات)

```sql
CREATE TABLE taxonomies (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    type_id             UUID NOT NULL REFERENCES taxonomy_types(id),
    parent_id           UUID NULL REFERENCES taxonomies(id),
    
    -- Hierarchy
    depth               INTEGER NOT NULL DEFAULT 0,
    path                VARCHAR(1000) NULL, -- Materialized path
    
    -- Identification
    slug                VARCHAR(255) NOT NULL,
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Appearance
    icon                VARCHAR(100) NULL,
    color               VARCHAR(20) NULL,
    image_id            UUID NULL REFERENCES media(id),
    
    -- Statistics
    content_count       INTEGER NOT NULL DEFAULT 0,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Metadata
    meta                JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(slug, type_id, deleted_at),
    CHECK (parent_id != id)
);

CREATE INDEX idx_taxonomies_type ON taxonomies(type_id);
CREATE INDEX idx_taxonomies_parent ON taxonomies(parent_id);
CREATE INDEX idx_taxonomies_path ON taxonomies(path);
```

### taxonomy_translations (ترجمات التصنيفات)

```sql
CREATE TABLE taxonomy_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    taxonomy_id         UUID NOT NULL REFERENCES taxonomies(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    name                VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL,
    description         TEXT NULL,
    
    -- SEO
    meta_title          VARCHAR(255) NULL,
    meta_description    TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(taxonomy_id, locale),
    UNIQUE(slug, locale)
);
```

### content_taxonomies (جدول وسيط - Polymorphic)

```sql
CREATE TABLE content_taxonomies (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Polymorphic Relation
    taggable_type       VARCHAR(50) NOT NULL, -- 'service', 'article', 'product'...
    taggable_id         UUID NOT NULL,
    
    -- Taxonomy
    taxonomy_id         UUID NOT NULL REFERENCES taxonomies(id) ON DELETE CASCADE,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE(taggable_type, taggable_id, taxonomy_id)
);

CREATE INDEX idx_content_tax_taggable ON content_taxonomies(taggable_type, taggable_id);
CREATE INDEX idx_content_tax_taxonomy ON content_taxonomies(taxonomy_id);
```

---

**نهاية الجزء الأول - يتبع في الجزء الثاني**
