# تحديد الأعمدة لكل كيان (Columns Definition) - الجزء الرابع
## النسخ، السجلات، الإشعارات، SEO، المهام

---

## 19. Revisions (النسخ السابقة)

```sql
CREATE TABLE revisions (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Polymorphic Relation
    revisionable_type   VARCHAR(50) NOT NULL,
    revisionable_id     UUID NOT NULL,
    
    -- Version
    version             INTEGER NOT NULL,
    
    -- Type
    type                VARCHAR(20) NOT NULL DEFAULT 'update',
                        -- ENUM: create, update, publish, unpublish, restore
    
    -- Data
    old_data            JSONB NULL,
    new_data            JSONB NOT NULL,
    diff                JSONB NULL,
    
    -- Summary
    summary             TEXT NULL,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE(revisionable_type, revisionable_id, version)
);

CREATE INDEX idx_revisions_revisionable ON revisions(revisionable_type, revisionable_id);
CREATE INDEX idx_revisions_created ON revisions(created_at DESC);
```

---

## 20. Activity Logs (سجل النشاطات)

```sql
CREATE TABLE activity_logs (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Log Name
    log_name            VARCHAR(50) NOT NULL DEFAULT 'default',
    
    -- Event
    event               VARCHAR(100) NOT NULL,
    description         TEXT NULL,
    
    -- Subject (Polymorphic)
    subject_type        VARCHAR(50) NULL,
    subject_id          UUID NULL,
    
    -- Causer (Polymorphic)
    causer_type         VARCHAR(50) NULL,
    causer_id           UUID NULL,
    
    -- Properties
    properties          JSONB NULL,
    old_values          JSONB NULL,
    new_values          JSONB NULL,
    
    -- Request Info
    ip_address          INET NULL,
    user_agent          TEXT NULL,
    request_id          UUID NULL,
    
    -- Timestamp
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_activity_subject ON activity_logs(subject_type, subject_id);
CREATE INDEX idx_activity_causer ON activity_logs(causer_type, causer_id);
CREATE INDEX idx_activity_event ON activity_logs(event);
CREATE INDEX idx_activity_date ON activity_logs(created_at DESC);
```

---

## 21. Audit Trails (سجل التدقيق)

```sql
CREATE TABLE audit_trails (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Entity
    entity_type         VARCHAR(50) NOT NULL,
    entity_id           UUID NOT NULL,
    
    -- Action
    action              VARCHAR(50) NOT NULL,
                        -- ENUM: create, update, delete, restore, 
                        --       publish, unpublish, login, logout
    
    -- User
    user_id             UUID NULL REFERENCES users(id),
    user_email          VARCHAR(255) NULL,
    user_name           VARCHAR(255) NULL,
    
    -- Changes
    changes             JSONB NULL,
    
    -- Context
    ip_address          INET NULL,
    user_agent          TEXT NULL,
    url                 VARCHAR(1000) NULL,
    method              VARCHAR(10) NULL,
    
    -- Timestamp
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_audit_entity ON audit_trails(entity_type, entity_id);
CREATE INDEX idx_audit_user ON audit_trails(user_id);
CREATE INDEX idx_audit_action ON audit_trails(action);
CREATE INDEX idx_audit_date ON audit_trails(created_at DESC);
```

---

## 22. Notifications (الإشعارات)

```sql
CREATE TABLE notifications (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Type
    type                VARCHAR(100) NOT NULL,
    
    -- Recipient
    user_id             UUID NOT NULL REFERENCES users(id),
    
    -- Related Entity (Polymorphic)
    notifiable_type     VARCHAR(50) NULL,
    notifiable_id       UUID NULL,
    
    -- Content
    data                JSONB NOT NULL,
    
    -- Status
    read_at             TIMESTAMP NULL,
    
    -- Channels
    channels            JSONB NULL, -- ['database', 'email', 'push']
    sent_channels       JSONB NULL,
    
    -- Timestamp
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_type ON notifications(type);
CREATE INDEX idx_notifications_read ON notifications(user_id, read_at);
CREATE INDEX idx_notifications_date ON notifications(created_at DESC);
```

### notification_templates (قوالب الإشعارات)

```sql
CREATE TABLE notification_templates (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    slug                VARCHAR(100) NOT NULL UNIQUE,
    
    -- Type
    type                VARCHAR(100) NOT NULL,
    
    -- Channels
    available_channels  JSONB NOT NULL, -- ['email', 'database', 'push', 'sms']
    
    -- Variables
    variables           JSONB NULL, -- [{name: 'user_name', type: 'string'}]
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    is_system           BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);
```

### notification_template_translations

```sql
CREATE TABLE notification_template_translations (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    template_id         UUID NOT NULL REFERENCES notification_templates(id) ON DELETE CASCADE,
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Email
    email_subject       VARCHAR(255) NULL,
    email_body          TEXT NULL,
    
    -- Push/Database
    title               VARCHAR(255) NULL,
    body                TEXT NULL,
    
    -- SMS
    sms_body            VARCHAR(500) NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(template_id, locale)
);
```

### email_logs (سجل الإيميلات)

```sql
CREATE TABLE email_logs (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Recipient
    to_email            VARCHAR(255) NOT NULL,
    to_name             VARCHAR(255) NULL,
    
    -- Sender
    from_email          VARCHAR(255) NOT NULL,
    from_name           VARCHAR(255) NULL,
    
    -- Content
    subject             VARCHAR(500) NOT NULL,
    body                TEXT NULL,
    
    -- Template
    template_id         UUID NULL REFERENCES notification_templates(id),
    
    -- Related
    user_id             UUID NULL REFERENCES users(id),
    notifiable_type     VARCHAR(50) NULL,
    notifiable_id       UUID NULL,
    
    -- Status
    status              VARCHAR(20) NOT NULL DEFAULT 'pending',
                        -- ENUM: pending, sent, delivered, bounced, failed
    
    -- Provider
    provider            VARCHAR(50) NULL,
    provider_message_id VARCHAR(255) NULL,
    
    -- Error
    error_message       TEXT NULL,
    
    -- Tracking
    opened_at           TIMESTAMP NULL,
    clicked_at          TIMESTAMP NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sent_at             TIMESTAMP NULL
);

CREATE INDEX idx_email_logs_to ON email_logs(to_email);
CREATE INDEX idx_email_logs_status ON email_logs(status);
CREATE INDEX idx_email_logs_date ON email_logs(created_at DESC);
```

---

## 23. Webhooks (نقاط الـ Webhook)

```sql
CREATE TABLE webhooks (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    name                VARCHAR(255) NOT NULL,
    
    -- Endpoint
    url                 VARCHAR(1000) NOT NULL,
    
    -- Events
    events              JSONB NOT NULL, -- ['article.published', 'order.created']
    
    -- Authentication
    secret              VARCHAR(255) NULL,
    headers             JSONB NULL,
    
    -- Settings
    timeout             INTEGER NOT NULL DEFAULT 30, -- seconds
    retry_count         INTEGER NOT NULL DEFAULT 3,
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Statistics
    success_count       INTEGER NOT NULL DEFAULT 0,
    failure_count       INTEGER NOT NULL DEFAULT 0,
    last_triggered_at   TIMESTAMP NULL,
    last_status         VARCHAR(20) NULL,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    deleted_at          TIMESTAMP NULL
);
```

### webhook_logs (سجل الـ Webhooks)

```sql
CREATE TABLE webhook_logs (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Relations
    webhook_id          UUID NOT NULL REFERENCES webhooks(id) ON DELETE CASCADE,
    
    -- Event
    event               VARCHAR(100) NOT NULL,
    
    -- Request
    request_headers     JSONB NULL,
    request_payload     JSONB NULL,
    
    -- Response
    response_status     INTEGER NULL,
    response_headers    JSONB NULL,
    response_body       TEXT NULL,
    
    -- Duration
    duration_ms         INTEGER NULL,
    
    -- Status
    status              VARCHAR(20) NOT NULL,
                        -- ENUM: pending, success, failed, retrying
    
    -- Retry
    attempt             INTEGER NOT NULL DEFAULT 1,
    next_retry_at       TIMESTAMP NULL,
    
    -- Error
    error_message       TEXT NULL,
    
    -- Timestamp
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_webhook_logs_webhook ON webhook_logs(webhook_id);
CREATE INDEX idx_webhook_logs_event ON webhook_logs(event);
CREATE INDEX idx_webhook_logs_status ON webhook_logs(status);
```

---

## 24. SEO (تحسين محركات البحث)

```sql
CREATE TABLE seo_metas (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Polymorphic Relation
    seoable_type        VARCHAR(50) NOT NULL,
    seoable_id          UUID NOT NULL,
    
    -- Locale
    locale              VARCHAR(10) NOT NULL REFERENCES languages(code),
    
    -- Basic SEO
    title               VARCHAR(255) NULL,
    description         TEXT NULL,
    keywords            VARCHAR(500) NULL,
    
    -- Open Graph
    og_title            VARCHAR(255) NULL,
    og_description      TEXT NULL,
    og_image_id         UUID NULL REFERENCES media(id),
    og_type             VARCHAR(50) NULL DEFAULT 'website',
    
    -- Twitter Card
    twitter_card        VARCHAR(50) NULL DEFAULT 'summary_large_image',
    twitter_title       VARCHAR(255) NULL,
    twitter_description TEXT NULL,
    twitter_image_id    UUID NULL REFERENCES media(id),
    
    -- Robots
    robots              VARCHAR(100) NULL, -- index, follow
    canonical_url       VARCHAR(1000) NULL,
    
    -- Schema
    schema_markup       JSONB NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL,
    
    -- Constraints
    UNIQUE(seoable_type, seoable_id, locale)
);

CREATE INDEX idx_seo_metas_seoable ON seo_metas(seoable_type, seoable_id);
```

### redirects (التحويلات)

```sql
CREATE TABLE redirects (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- URLs
    source_url          VARCHAR(1000) NOT NULL,
    target_url          VARCHAR(1000) NOT NULL,
    
    -- Type
    status_code         INTEGER NOT NULL DEFAULT 301,
                        -- 301 = Permanent, 302 = Temporary
    
    -- Matching
    is_regex            BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Statistics
    hit_count           INTEGER NOT NULL DEFAULT 0,
    last_hit_at         TIMESTAMP NULL,
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Validity
    expires_at          TIMESTAMP NULL,
    
    -- Relations (FK)
    created_by          UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);

CREATE INDEX idx_redirects_source ON redirects(source_url);
CREATE INDEX idx_redirects_active ON redirects(is_active);
```

---

## 25. Analytics (التحليلات)

```sql
CREATE TABLE page_views (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Page
    url                 VARCHAR(1000) NOT NULL,
    
    -- Entity (Polymorphic)
    viewable_type       VARCHAR(50) NULL,
    viewable_id         UUID NULL,
    
    -- Visitor
    visitor_id          VARCHAR(100) NULL, -- anonymous ID
    user_id             UUID NULL REFERENCES users(id),
    session_id          VARCHAR(100) NULL,
    
    -- Source
    referrer            VARCHAR(1000) NULL,
    utm_source          VARCHAR(100) NULL,
    utm_medium          VARCHAR(100) NULL,
    utm_campaign        VARCHAR(100) NULL,
    
    -- Device
    device_type         VARCHAR(20) NULL, -- desktop, mobile, tablet
    browser             VARCHAR(50) NULL,
    os                  VARCHAR(50) NULL,
    
    -- Location
    country             VARCHAR(2) NULL,
    city                VARCHAR(100) NULL,
    
    -- Request
    ip_address          INET NULL,
    user_agent          TEXT NULL,
    
    -- Timestamp
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_page_views_url ON page_views(url);
CREATE INDEX idx_page_views_viewable ON page_views(viewable_type, viewable_id);
CREATE INDEX idx_page_views_date ON page_views(created_at);
```

### search_logs (سجل البحث)

```sql
CREATE TABLE search_logs (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Query
    query               VARCHAR(500) NOT NULL,
    
    -- Results
    results_count       INTEGER NOT NULL DEFAULT 0,
    
    -- User
    user_id             UUID NULL REFERENCES users(id),
    session_id          VARCHAR(100) NULL,
    
    -- Context
    locale              VARCHAR(10) NULL,
    filters             JSONB NULL,
    
    -- Request
    ip_address          INET NULL,
    
    -- Timestamp
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_search_logs_query ON search_logs(query);
CREATE INDEX idx_search_logs_date ON search_logs(created_at);
```

---

## 26. Jobs & Scheduling (المهام)

```sql
CREATE TABLE scheduled_tasks (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Identification
    name                VARCHAR(255) NOT NULL,
    
    -- Command
    command             VARCHAR(500) NOT NULL,
    parameters          JSONB NULL,
    
    -- Schedule (cron expression)
    cron_expression     VARCHAR(100) NOT NULL,
    timezone            VARCHAR(50) NOT NULL DEFAULT 'UTC',
    
    -- Control
    is_active           BOOLEAN NOT NULL DEFAULT TRUE,
    
    -- Execution
    last_run_at         TIMESTAMP NULL,
    next_run_at         TIMESTAMP NULL,
    last_run_status     VARCHAR(20) NULL,
    last_run_duration   INTEGER NULL, -- ms
    last_run_output     TEXT NULL,
    
    -- Statistics
    run_count           INTEGER NOT NULL DEFAULT 0,
    failure_count       INTEGER NOT NULL DEFAULT 0,
    
    -- Notifications
    notify_on_failure   BOOLEAN NOT NULL DEFAULT TRUE,
    notify_emails       VARCHAR(500) NULL,
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP NULL
);
```

### failed_jobs (المهام الفاشلة)

```sql
CREATE TABLE failed_jobs (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- Job Info
    uuid                UUID NOT NULL UNIQUE,
    connection          VARCHAR(100) NOT NULL,
    queue               VARCHAR(100) NOT NULL,
    
    -- Payload
    payload             TEXT NOT NULL,
    
    -- Exception
    exception           TEXT NOT NULL,
    
    -- Timestamp
    failed_at           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_failed_jobs_queue ON failed_jobs(queue);
```

---

## 27. Sessions (الجلسات)

```sql
CREATE TABLE user_sessions (
    -- Primary
    id                  VARCHAR(255) PRIMARY KEY,
    
    -- User
    user_id             UUID NULL REFERENCES users(id) ON DELETE CASCADE,
    
    -- Data
    payload             TEXT NOT NULL,
    
    -- Request Info
    ip_address          INET NULL,
    user_agent          TEXT NULL,
    
    -- Activity
    last_activity       INTEGER NOT NULL,
    
    -- Device
    device_name         VARCHAR(255) NULL,
    is_current          BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_sessions_user ON user_sessions(user_id);
CREATE INDEX idx_sessions_activity ON user_sessions(last_activity);
```

### password_resets (إعادة تعيين كلمة المرور)

```sql
CREATE TABLE password_resets (
    -- Email
    email               VARCHAR(255) NOT NULL,
    
    -- Token
    token               VARCHAR(255) NOT NULL,
    
    -- Timestamp
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Index
    PRIMARY KEY (email, token)
);
```

### user_bans (حظر المستخدمين)

```sql
CREATE TABLE user_bans (
    -- Primary
    id                  UUID PRIMARY KEY,
    
    -- User
    user_id             UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    
    -- Ban Info
    reason              TEXT NULL,
    
    -- Duration
    banned_until        TIMESTAMP NULL, -- NULL = permanent
    
    -- Relations (FK)
    banned_by           UUID NOT NULL REFERENCES users(id),
    
    -- Timestamps
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    lifted_at           TIMESTAMP NULL,
    lifted_by           UUID NULL REFERENCES users(id)
);

CREATE INDEX idx_user_bans_user ON user_bans(user_id);
```

---

**نهاية تحديد الأعمدة - يتبع مخطط ERD**
