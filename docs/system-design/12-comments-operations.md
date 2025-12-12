# تحليل عمليات التعليقات (Comments Operations)

## 📋 نظرة عامة
التعليقات هي محتوى من المستخدمين على المقالات والمنتجات وغيرها. تدعم التداخل، الإشراف، والتقييم.

---

## 🔄 State Machine Diagram

```
┌──────────┐   submit   ┌─────────────────┐
│  (new)   │───────────▶│ pending_review  │
└──────────┘            └─────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
       ┌───────────┐   ┌───────────┐   ┌───────────┐
       │ approved  │   │ in_spam   │   │ rejected  │
       └───────────┘   └───────────┘   └───────────┘
              │               │
              ▼               ▼
       ┌───────────┐   ┌───────────┐
       │ published │   │ confirmed │
       └───────────┘   │ (not spam)│
              │        └───────────┘
              │               │
              ▼               ▼
       ┌───────────┐   ┌───────────┐
       │  hidden   │   │ published │
       └───────────┘   └───────────┘
              │
              ▼
       ┌──────────────┐
       │ soft_deleted │
       └──────────────┘
```

---

## 📌 العملية 1: إضافة تعليق (Create Comment)

### 1. اسم العملية
`comment.create`

### 2. الهدف
إضافة تعليق جديد على محتوى.

### 3. الشروط المسبقة
- المحتوى يقبل التعليقات
- المستخدم مسجل أو التعليقات مفتوحة للجميع
- Rate limit غير متجاوز

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Check content allows comments
    ├── Check comments not closed
    ├── Validate content length
    ├── Check rate limit
    └── CAPTCHA (if guest)

[2] Spam Detection
    ├── Akismet/spam service check
    ├── Keyword blacklist
    ├── Link density check
    └── User reputation check

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Generate UUID
    │
    [5] Create Comment Record
    │   └── INSERT INTO comments (id, commentable_type, commentable_id, user_id, parent_id, content, status, ip_address, ...)
    │
    [6] Set Initial Status
    │   ├── If spam detected → in_spam
    │   ├── If moderation required → pending_review
    │   ├── If trusted user → approved
    │   └── If auto-approve enabled → approved
    │
    [7] If has parent (reply):
    │   └── Update parent reply_count
    │
COMMIT TRANSACTION ───────────────────────────────────

[8] Dispatch Events
    └── CommentSubmitted event

[9] Queue Jobs
    ├── NotifyContentOwnerJob
    ├── NotifyParentCommenterJob (if reply)
    ├── NotifyModeratorsJob (if pending)
    └── UpdateCommentCountJob
```

### 5. الآثار الجانبية
- إنشاء التعليق
- تحديث عداد التعليقات
- إشعار الأطراف المعنية

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Comments Closed | Return 403 |
| Rate Limited | Return 429 |
| Spam Detected | Accept silently, mark as spam |
| Content Too Long | Return 422 |
| Banned User | Return 403 |

### 7. Security Considerations
- تنظيف HTML
- منع XSS
- Rate limiting per user/IP
- تسجيل IP للتتبع

### 8. API Endpoint

```http
POST /api/v1/comments
{
  "commentable_type": "article",
  "commentable_id": "article-uuid",
  "parent_id": null,
  "content": "تعليق رائع على المقال...",
  "author_name": "زائر",
  "author_email": "guest@example.com"
}
```

### 9. Webhook Payload

```json
{
  "event": "comment.submitted",
  "payload": {
    "id": "uuid",
    "content_type": "article",
    "content_id": "uuid",
    "is_reply": false,
    "status": "pending_review",
    "author": {
      "type": "guest",
      "name": "زائر"
    }
  }
}
```

---

## 📌 العملية 2: الموافقة (Approve)

```http
POST /api/v1/comments/{id}/approve
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status → approved/published
    │
    [3] Set approved_at, approved_by
    │
    [4] If first approved comment by user:
    │   └── Mark user as trusted (faster approval next time)
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs
    ├── UpdateCommentCountJob (increment visible)
    ├── NotifyCommenterJob (approved)
    └── InvalidateCacheJob
```

---

## 📌 العملية 3: الرفض (Reject)

```http
POST /api/v1/comments/{id}/reject
{
  "reason": "inappropriate_content"
}
```

---

## 📌 العملية 4: تأكيد ليس سبام (Not Spam)

```http
POST /api/v1/comments/{id}/not-spam
```

**خطوات التنفيذ:**
```
[1] Update status → pending_review or approved

[2] Report false positive to spam service

[3] Improve user reputation
```

---

## 📌 العملية 5: الإبلاغ عن تعليق (Report Comment)

```http
POST /api/v1/comments/{id}/report
{
  "reason": "offensive",
  "details": "..."
}
```

**خطوات التنفيذ:**
```
[1] Create report record

[2] If report_count > threshold:
    └── Auto-hide for review

[3] Notify moderators
```

---

## 📌 العملية 6: إخفاء تعليق (Hide Comment)

```http
POST /api/v1/comments/{id}/hide
{
  "reason": "under_review"
}
```

---

## 📌 العملية 7: الرد على تعليق (Reply to Comment)

```http
POST /api/v1/comments
{
  "parent_id": "comment-uuid",
  ... other fields
}
```

**ملاحظات:**
- أقصى عمق للردود: 3 مستويات (قابل للتكوين)
- إشعار صاحب التعليق الأصلي

---

## 📌 العملية 8: تعديل تعليق (Edit Comment)

```http
PUT /api/v1/comments/{id}
{
  "content": "محتوى معدل..."
}
```

**القيود:**
- المالك فقط أو المشرف
- خلال فترة زمنية (مثلاً 15 دقيقة)
- تسجيل التعديل

---

## 📌 العملية 9: حذف تعليق (Delete Comment)

```http
DELETE /api/v1/comments/{id}
{
  "handle_replies": "keep"
}
```

**الخيارات:**
- `keep`: إبقاء الردود مع "[تم حذف هذا التعليق]"
- `delete`: حذف مع جميع الردود

---

## 📌 العملية 10: التصويت (Vote/React)

```http
POST /api/v1/comments/{id}/vote
{
  "type": "like"
}
```

**أنواع التفاعل:**
- `like` / `dislike`
- `helpful` / `not_helpful`
- Reactions: 👍 ❤️ 😂 😮 😢 😡

---

## 📌 العملية 11: تثبيت تعليق (Pin Comment)

```http
POST /api/v1/comments/{id}/pin
```

---

## 📌 العملية 12: قفل التعليقات (Lock Comments)

```http
POST /api/v1/{content_type}/{id}/lock-comments
{
  "reason": "off_topic_discussion"
}
```

---

## 📌 العملية 13: حظر مستخدم (Ban User from Commenting)

```http
POST /api/v1/comments/ban
{
  "user_id": "uuid",
  "duration": "permanent",
  "reason": "repeated_violations"
}
```

---

## 📌 العملية 14: الموافقة الجماعية (Bulk Approve)

```http
POST /api/v1/comments/bulk-approve
{
  "ids": ["uuid1", "uuid2"]
}
```

---

## 📌 العملية 15: تنظيف السبام (Cleanup Spam)

```http
POST /api/v1/comments/cleanup-spam
{
  "older_than_days": 30
}
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                     Comment Lifecycle Flow                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  User           System          Moderator       Author          │
│    │              │                │              │              │
│    │── Submit ───▶│                │              │              │
│    │              │── Spam Check ──│              │              │
│    │              │                │              │              │
│    │              │── Notify ─────▶│              │              │
│    │              │── Notify ──────────────────────▶│              │
│    │              │                │              │              │
│    │              │◀── Approve ────│              │              │
│    │◀── Notify ───│                │              │              │
│    │              │                │              │              │
│    │              │                │     ◀── Reply│              │
│    │◀── Notify ───│                │              │              │
│    │   (reply)    │                │              │              │
│    │              │                │              │              │
│    │── Report ───▶│                │              │              │
│    │              │── Notify ─────▶│              │              │
│    │              │◀── Hide ───────│              │              │
│    │              │                │              │              │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات التعليقات**
