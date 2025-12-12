# تحديد الأعمدة لكل كيان (Columns Definition) - الجزء الثاني
## الوسائط، القوائم، النماذج، التعليقات، الفعاليات

---

## 7. Media (الوسائط)

```sql
CREATE TABLE media (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- File Info
    filename            VARCHAR(255) NOT NULL,
    original_filename   VARCHAR(255) NOT NULL,
    mime_type           VARCHAR(100) NOT NULL,
    extension           VARCHAR(20) NOT NULL,
    size                BIGINT NOT NULL, -- bytes
    
    -- Storage
    disk                VARCHAR(50) NOT NULL DEFAULT 'public',
    path                VARCHAR(500) NOT NULL,
    url                 VARCHAR(1000) NULL,
    
    -- Type
    type                VARCHAR(50) NOT NULL,
                        -- ENUM: image, video, audio, document, archive, other
    
    -- Image Specific
    width               INTEGER NULL,
    height              INTEGER NULL,
    
    -- Video/Audio Specific
    duration            INTEGER NULL, -- seconds
    
    -- Processing
    status              VARCHAR(20) NOT NULL DEFAULT 'processing',
                        -- ENUM: processing, ready, failed, quarantine
    
    -- Organization
    folder_id           UUID NULL REFERENCES media_folders(id),
    
    -- Deduplication
    hash                VARCHAR(64) NULL, -- SHA-256
    
    -- Control
    is_private          BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Metadata
    meta                JSONB NULL, -- EXIF, etc.
    
    -- Relations (FK)
    uploaded_by         UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL
);

CREATE INDEX idx_media_type ON media(type);
CREATE INDEX idx_media_folder ON media(folder_id);
CREATE INDEX idx_media_hash ON media(hash);
CREATE INDEX idx_media_mime ON media(mime_type);
```

### media_translations (ترجمات الوسائط)

```sql
CREATE TABLE media_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    media_id            UUID NOT NULL REFERENCES media(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    title               VARCHAR(255) NULL,
    alt_text            VARCHAR(500) NULL,
    caption             TEXT NULL,
    description         TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(media_id, locale)
);
```

### media_variants (متغيرات الوسائط)

```sql
CREATE TABLE media_variants (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    media_id            UUID NOT NULL REFERENCES media(id) ON DELETE CASCADE,
    
    -- Variant Info
    name                VARCHAR(50) NOT NULL, -- thumbnail, medium, large
    
    -- File Info
    filename            VARCHAR(255) NOT NULL,
    path                VARCHAR(500) NOT NULL,
    url                 VARCHAR(1000) NULL,
    size                BIGINT NOT NULL,
    
    -- Dimensions
    width               INTEGER NULL,
    height              INTEGER NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE(media_id, name)
);
```

### media_folders (مجلدات الوسائط)

```sql
CREATE TABLE media_folders (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Hierarchy
    parent_id           UUID NULL REFERENCES media_folders(id),
    depth               INTEGER NOT NULL DEFAULT 0,
    path                VARCHAR(1000) NULL,
    
    -- Info
    name                VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL,
    
    -- Statistics
    files_count         INTEGER NOT NULL DEFAULT 0,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL,
    
    -- Constraints
    CHECK (parent_id != id)
);
```

### content_media (جدول وسيط - Polymorphic)

```sql
CREATE TABLE content_media (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Polymorphic Relation
    mediable_type       VARCHAR(50) NOT NULL,
    mediable_id         UUID NOT NULL,
    
    -- Media
    media_id            UUID NOT NULL REFERENCES media(id) ON DELETE CASCADE,
    
    -- Collection
    collection          VARCHAR(50) NOT NULL DEFAULT 'default',
                        -- featured, gallery, documents, etc.
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Custom Data
    meta                JSONB NULL, -- {focal_point: {x, y}, crop: {...}}
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE(mediable_type, mediable_id, media_id, collection)
);

CREATE INDEX idx_content_media_mediable ON content_media(mediable_type, mediable_id);
CREATE INDEX idx_content_media_collection ON content_media(collection);
```

---

## 8. Menus (القوائم)

```sql
CREATE TABLE menus (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    slug                VARCHAR(100) NOT NULL UNIQUE,
    
    -- Control
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
    
    -- Location
    location            VARCHAR(50) NULL,
                        -- header, footer, sidebar, mobile
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    updated_by          UUID NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL
);
```

### menu_translations (ترجمات القوائم)

```sql
CREATE TABLE menu_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    menu_id             UUID NOT NULL REFERENCES menus(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    name                VARCHAR(255) NOT NULL,
    description         TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(menu_id, locale)
);
```

### menu_items (عناصر القوائم)

```sql
CREATE TABLE menu_items (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    menu_id             UUID NOT NULL REFERENCES menus(id) ON DELETE CASCADE,
    parent_id           UUID NULL REFERENCES menu_items(id),
    
    -- Type
    type                VARCHAR(50) NOT NULL,
                        -- ENUM: page, article, category, custom, placeholder
    
    -- Link Target (Polymorphic)
    linkable_type       VARCHAR(50) NULL, -- page, article, taxonomy, product
    linkable_id         UUID NULL,
    
    -- Custom URL
    url                 VARCHAR(1000) NULL,
    
    -- Behavior
    target              VARCHAR(20) NOT NULL DEFAULT '_self',
                        -- _self, _blank
    
    -- Appearance
    icon                VARCHAR(100) NULL,
    css_class           VARCHAR(255) NULL,
    
    -- Hierarchy
    depth               INTEGER NOT NULL DEFAULT 0,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Visibility
    visibility          JSONB NULL, -- {roles: [], logged_in: true}
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    CHECK (parent_id != id)
);

CREATE INDEX idx_menu_items_menu ON menu_items(menu_id);
CREATE INDEX idx_menu_items_parent ON menu_items(parent_id);
```

### menu_item_translations (ترجمات عناصر القوائم)

```sql
CREATE TABLE menu_item_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    item_id             UUID NOT NULL REFERENCES menu_items(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    label               VARCHAR(255) NOT NULL,
    title               VARCHAR(255) NULL, -- HTML title attribute
    description         TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(item_id, locale)
);
```

---

## 9. Forms (النماذج)

```sql
CREATE TABLE forms (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    slug                VARCHAR(100) NOT NULL UNIQUE,
    
    -- Type
    type                VARCHAR(50) NOT NULL DEFAULT 'contact',
                        -- ENUM: contact, newsletter, survey, application
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Settings
    requires_captcha    BOOLEAN NOT NULL DEFAULT TRUE,
    requires_auth       BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Rate Limiting
    rate_limit          INTEGER NULL, -- submissions per IP per hour
    
    -- Notifications
    notification_emails VARCHAR(1000) NULL, -- comma-separated
    
    -- Auto Response
    send_confirmation   BOOLEAN NOT NULL DEFAULT FALSE,
    confirmation_template_id UUID NULL,
    
    -- Redirect
    success_redirect    VARCHAR(500) NULL,
    
    -- Storage
    store_submissions   BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Metadata
    settings            JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL
);
```

### form_fields (حقول النماذج)

```sql
CREATE TABLE form_fields (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    form_id             UUID NOT NULL REFERENCES forms(id) ON DELETE CASCADE,
    
    -- Identification
    name                VARCHAR(100) NOT NULL, -- field_email, field_name
    
    -- Type
    type                VARCHAR(50) NOT NULL,
                        -- ENUM: text, email, phone, textarea, select, 
                        --       checkbox, radio, file, date, number, hidden
    
    -- Validation
    is_required         BOOLEAN NOT NULL DEFAULT FALSE,
    validation_rules    JSONB NULL, -- {min: 2, max: 100, pattern: '...'}
    
    -- Options (for select, radio, checkbox)
    options             JSONB NULL, -- [{value: '1', label: 'Option 1'}]
    
    -- File Upload
    allowed_extensions  VARCHAR(255) NULL,
    max_file_size       INTEGER NULL, -- KB
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Appearance
    width               VARCHAR(20) NULL DEFAULT 'full', -- full, half, third
    css_class           VARCHAR(255) NULL,
    
    -- Conditional Logic
    conditions          JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(form_id, name)
);
```

### form_field_translations

```sql
CREATE TABLE form_field_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    field_id            UUID NOT NULL REFERENCES form_fields(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    label               VARCHAR(255) NOT NULL,
    placeholder         VARCHAR(255) NULL,
    help_text           TEXT NULL,
    error_message       VARCHAR(500) NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(field_id, locale)
);
```

### form_submissions (إرسالات النماذج)

```sql
CREATE TABLE form_submissions (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    form_id             UUID NOT NULL REFERENCES forms(id),
    
    -- Status
    status              VARCHAR(20) NOT NULL DEFAULT 'pending',
                        -- ENUM: pending, new, opened, in_progress, 
                        --       on_hold, completed, spam, archived
    
    -- Spam
    is_spam             BOOLEAN NOT NULL DEFAULT FALSE,
    spam_score          DECIMAL(5,2) NULL,
    
    -- Assignment
    assigned_to         UUID NULL REFERENCES users(id),
    assigned_at         TIMESTAMP NULL,
    
    -- Submitter Info
    user_id             UUID NULL REFERENCES users(id),
    ip_address          INET NULL,
    user_agent          TEXT NULL,
    
    -- Source
    source_url          VARCHAR(1000) NULL,
    referrer            VARCHAR(1000) NULL,
    utm_source          VARCHAR(100) NULL,
    utm_medium          VARCHAR(100) NULL,
    utm_campaign        VARCHAR(100) NULL,
    
    -- Locale
    locale              VARCHAR(10) NULL,
    
    -- Data (denormalized for quick access)
    data                JSONB NOT NULL,
    
    -- Tracking
    tracking_code       VARCHAR(50) NULL UNIQUE,
    
    -- Processing
    opened_at           TIMESTAMP NULL,
    opened_by           UUID NULL REFERENCES users(id),
    completed_at        TIMESTAMP NULL,
    completed_by        UUID NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL
);

CREATE INDEX idx_submissions_form ON form_submissions(form_id);
CREATE INDEX idx_submissions_status ON form_submissions(status);
CREATE INDEX idx_submissions_assigned ON form_submissions(assigned_to);
CREATE INDEX idx_submissions_date ON form_submissions(created_at DESC);
```

---

## 10. Comments (التعليقات)

```sql
CREATE TABLE comments (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Polymorphic Relation
    commentable_type    VARCHAR(50) NOT NULL, -- article, product, event
    commentable_id      UUID NOT NULL,
    
    -- Hierarchy (for replies)
    parent_id           UUID NULL REFERENCES comments(id),
    depth               INTEGER NOT NULL DEFAULT 0,
    
    -- Content
    content             TEXT NOT NULL,
    
    -- Author
    user_id             UUID NULL REFERENCES users(id),
    author_name         VARCHAR(100) NULL, -- for guests
    author_email        VARCHAR(255) NULL, -- for guests
    
    -- Status
    status              VARCHAR(20) NOT NULL DEFAULT 'pending',
                        -- ENUM: pending, approved, rejected, spam, hidden
    
    -- Spam
    is_spam             BOOLEAN NOT NULL DEFAULT FALSE,
    spam_score          DECIMAL(5,2) NULL,
    
    -- Votes
    upvotes             INTEGER NOT NULL DEFAULT 0,
    downvotes           INTEGER NOT NULL DEFAULT 0,
    
    -- Reports
    report_count        INTEGER NOT NULL DEFAULT 0,
    
    -- Pinning
    is_pinned           BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Source
    ip_address          INET NULL,
    user_agent          TEXT NULL,
    
    -- Moderation
    approved_at         TIMESTAMP NULL,
    approved_by         UUID NULL REFERENCES users(id),
    
    -- Editing
    edited_at           TIMESTAMP NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL,
    
    -- Constraints
    CHECK (parent_id != id)
);

CREATE INDEX idx_comments_commentable ON comments(commentable_type, commentable_id);
CREATE INDEX idx_comments_parent ON comments(parent_id);
CREATE INDEX idx_comments_status ON comments(status);
CREATE INDEX idx_comments_user ON comments(user_id);
```

### comment_votes (تصويتات التعليقات)

```sql
CREATE TABLE comment_votes (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    comment_id          UUID NOT NULL REFERENCES comments(id) ON DELETE CASCADE,
    user_id             UUID NOT NULL REFERENCES users(id),
    
    -- Vote
    vote                SMALLINT NOT NULL, -- 1 = up, -1 = down
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE(comment_id, user_id),
    CHECK (vote IN (-1, 1))
);
```

---

## 11. Events (الفعاليات)

```sql
CREATE TABLE events (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Type
    type                VARCHAR(50) NOT NULL DEFAULT 'conference',
                        -- ENUM: conference, workshop, webinar, meetup, training
    
    -- Venue Type
    venue_type          VARCHAR(20) NOT NULL DEFAULT 'physical',
                        -- ENUM: physical, virtual, hybrid
    
    -- Control
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
    
    -- Registration
    registration_status VARCHAR(20) NOT NULL DEFAULT 'closed',
                        -- ENUM: closed, open, full, waitlist
    registration_opens_at  TIMESTAMP NULL,
    registration_closes_at TIMESTAMP NULL,
    
    -- Capacity
    capacity            INTEGER NULL,
    registered_count    INTEGER NOT NULL DEFAULT 0,
    attended_count      INTEGER NOT NULL DEFAULT 0,
    
    -- Timing
    starts_at           TIMESTAMP NOT NULL,
    ends_at             TIMESTAMP NOT NULL,
    timezone            VARCHAR(50) NOT NULL DEFAULT 'UTC',
    
    -- Actual Timing
    actual_start_at     TIMESTAMP NULL,
    actual_end_at       TIMESTAMP NULL,
    
    -- Location (Physical)
    venue_name          VARCHAR(255) NULL,
    venue_address       TEXT NULL,
    venue_coordinates   JSONB NULL, -- {lat, lng}
    
    -- Location (Virtual)
    virtual_url         VARCHAR(500) NULL,
    virtual_platform    VARCHAR(50) NULL, -- zoom, teams, meet
    
    -- Pricing
    is_free             BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Versioning
    version             INTEGER NOT NULL DEFAULT 1,
    
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
    published_at        TIMESTAMP NULL,
    cancelled_at        TIMESTAMP NULL
);

CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_events_dates ON events(starts_at, ends_at);
CREATE INDEX idx_events_registration ON events(registration_status);
```

### event_translations (ترجمات الفعاليات)

```sql
CREATE TABLE event_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    event_id            UUID NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    title               VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL,
    excerpt             TEXT NULL,
    description         TEXT NULL,
    agenda              JSONB NULL,
    
    -- SEO
    meta_title          VARCHAR(255) NULL,
    meta_description    TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(event_id, locale),
    UNIQUE(slug, locale)
);
```

### event_ticket_types (أنواع التذاكر)

```sql
CREATE TABLE event_ticket_types (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    event_id            UUID NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    
    -- Identification
    slug                VARCHAR(100) NOT NULL,
    
    -- Quantity
    quantity            INTEGER NULL, -- NULL = unlimited
    sold_count          INTEGER NOT NULL DEFAULT 0,
    reserved_count      INTEGER NOT NULL DEFAULT 0,
    
    -- Availability
    available_from      TIMESTAMP NULL,
    available_until     TIMESTAMP NULL,
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Order
    sort_order          INTEGER NOT NULL DEFAULT 0,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(event_id, slug)
);
```

### event_ticket_type_translations

```sql
CREATE TABLE event_ticket_type_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    ticket_type_id      UUID NOT NULL REFERENCES event_ticket_types(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Translatable
    name                VARCHAR(255) NOT NULL,
    description         TEXT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(ticket_type_id, locale)
);
```

### event_ticket_prices (أسعار التذاكر)

```sql
CREATE TABLE event_ticket_prices (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    ticket_type_id      UUID NOT NULL REFERENCES event_ticket_types(id) ON DELETE CASCADE,
    currency_id         UUID NOT NULL REFERENCES currencies(id),
    
    -- Pricing
    amount              DECIMAL(15,4) NOT NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(ticket_type_id, currency_id),
    CHECK (amount >= 0)
);
```

### event_registrations (تسجيلات الفعاليات)

```sql
CREATE TABLE event_registrations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    event_id            UUID NOT NULL REFERENCES events(id),
    user_id             UUID NULL REFERENCES users(id),
    
    -- Status
    status              VARCHAR(20) NOT NULL DEFAULT 'pending',
                        -- ENUM: pending, confirmed, cancelled, 
                        --       refunded, no_show, attended
    
    -- Attendee Info
    attendee_name       VARCHAR(255) NOT NULL,
    attendee_email      VARCHAR(255) NOT NULL,
    attendee_phone      VARCHAR(50) NULL,
    
    -- Custom Fields
    custom_data         JSONB NULL,
    
    -- Payment
    payment_status      VARCHAR(20) NULL,
    payment_amount      DECIMAL(15,4) NULL,
    payment_currency    UUID NULL REFERENCES currencies(id),
    
    -- Coupon
    coupon_id           UUID NULL REFERENCES coupons(id),
    discount_amount     DECIMAL(15,4) NULL,
    
    -- Confirmation
    confirmation_code   VARCHAR(50) NOT NULL UNIQUE,
    confirmed_at        TIMESTAMP NULL,
    
    -- Cancellation
    cancelled_at        TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    
    -- Check-in
    checked_in_at       TIMESTAMP NULL,
    checked_in_by       UUID NULL REFERENCES users(id),
    
    -- Source
    source              VARCHAR(50) NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);

CREATE INDEX idx_registrations_event ON event_registrations(event_id);
CREATE INDEX idx_registrations_user ON event_registrations(user_id);
CREATE INDEX idx_registrations_status ON event_registrations(status);
CREATE INDEX idx_registrations_email ON event_registrations(attendee_email);
```

---

**نهاية الجزء الثاني - يتبع في الجزء الثالث**
