# تحليل عمليات الفعاليات (Event Operations)

## 📋 نظرة عامة
الفعاليات هي كيانات زمنية تمثل المؤتمرات، الورش، الندوات، والأحداث. تدعم التسجيل، التذاكر، والبث المباشر.

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
                              ┌─────────────────┬──────────────┘
                              ▼                 ▼
                        ┌───────────┐    ┌───────────────┐
                        │ scheduled │    │   published   │
                        └───────────┘    └───────────────┘
                              │                 │
                              └────────┬────────┘
                                       ▼
                            ┌────────────────────┐
                            │ registration_open  │
                            └────────────────────┘
                                       │
                    ┌──────────────────┼──────────────────┐
                    ▼                  ▼                  ▼
             ┌───────────┐     ┌─────────────┐    ┌────────────┐
             │  soldout  │     │registration │    │  ongoing   │
             │           │     │   closed    │    │            │
             └───────────┘     └─────────────┘    └────────────┘
                                                         │
                                                         ▼
                                                 ┌────────────┐
                                                 │ completed  │
                                                 └────────────┘
                                                         │
                                          ┌──────────────┴──────────────┐
                                          ▼                             ▼
                                   ┌───────────┐                 ┌──────────────┐
                                   │ archived  │                 │ soft_deleted │
                                   └───────────┘                 └──────────────┘
```

---

## 📌 العملية 1: إنشاء فعالية (Create Event)

### 1. اسم العملية
`event.create`

### 2. الهدف
إنشاء فعالية جديدة مع دعم التذاكر والتسجيل.

### 3. الشروط المسبقة
- صلاحية `event.create`
- التواريخ صالحة (البداية قبل النهاية)
- المكان متاح (إذا كان حضورياً)

### 4. خطوات التنفيذ

```
[1] Validate Request
    ├── Validate date range
    ├── Validate venue availability
    ├── Validate ticket types
    └── Validate speakers/presenters exist

[2] Authorization Check
    └── Gate::authorize('event.create')

[3] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [4] Generate UUID
    │
    [5] Create Event Record
    │   └── INSERT INTO events (id, type, venue_type, start_at, end_at, timezone, capacity, status, ...)
    │
    [6] Create Translation Records
    │   └── INSERT INTO event_translations (event_id, locale, title, slug, description, agenda, ...)
    │
    [7] Create Initial Revision
    │
    [8] Create Ticket Types
    │   └── INSERT INTO event_tickets (event_id, name, price, currency_id, quantity, ...)
    │
    [9] Create Sessions/Agenda
    │   └── INSERT INTO event_sessions (event_id, start_at, end_at, speaker_id, ...)
    │
    [10] Link Speakers
    │    └── INSERT INTO event_speakers
    │
    [11] Link Sponsors
    │    └── INSERT INTO event_sponsors
    │
    [12] Process Media
    │    ├── Banner images
    │    ├── Speaker photos
    │    └── Venue photos
    │
    [13] Create Registration Form (if custom)
    │
COMMIT TRANSACTION ───────────────────────────────────

[14] Dispatch Events
     └── EventCreated event

[15] Queue Jobs
     ├── GenerateEventCalendarJob
     ├── IndexSearchJob
     └── NotifySubscribersJob (upcoming events)
```

### 5. الآثار الجانبية
- إنشاء سجل الفعالية
- إنشاء أنواع التذاكر
- إنشاء جدول الجلسات
- توليد روابط التقويم

### 6. التعامل مع الفشل

| نوع الفشل | الاستجابة |
|-----------|----------|
| Invalid Date Range | Return 422 |
| Venue Unavailable | Return 409 + alternatives |
| Overlapping Event | Return 409 |
| Speaker Not Found | Return 422 |

### 7. Security Considerations
- التحقق من صلاحية إنشاء تذاكر مدفوعة
- التحقق من إعدادات الدفع

### 8. API Endpoint

```http
POST /api/v1/events
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "conference",
  "venue_type": "hybrid",
  "start_at": "2024-03-15T09:00:00Z",
  "end_at": "2024-03-15T17:00:00Z",
  "timezone": "Asia/Riyadh",
  "venue": {
    "name": "مركز المؤتمرات",
    "address": "...",
    "coordinates": { "lat": 24.7, "lng": 46.7 }
  },
  "capacity": 500,
  "translations": {
    "ar": {
      "title": "مؤتمر التقنية 2024",
      "slug": "tech-conference-2024",
      "description": "...",
      "agenda": [...]
    }
  },
  "tickets": [
    { "name": "عادي", "price": 100, "currency": "SAR", "quantity": 400 },
    { "name": "VIP", "price": 500, "currency": "SAR", "quantity": 100 }
  ],
  "sessions": [
    {
      "start_at": "2024-03-15T10:00:00Z",
      "duration_minutes": 60,
      "speaker_id": "uuid",
      "title": { "ar": "جلسة افتتاحية" }
    }
  ],
  "registration_required": true,
  "registration_deadline": "2024-03-14T23:59:59Z"
}
```

### 9. Webhook Payload

```json
{
  "event": "event.created",
  "timestamp": "2024-01-15T10:00:00Z",
  "payload": {
    "id": "uuid",
    "type": "conference",
    "start_at": "2024-03-15T09:00:00Z",
    "capacity": 500,
    "ticket_types": 2,
    "venue_type": "hybrid"
  }
}
```

---

## 📌 العمليات 2-7: دورة الحياة الأساسية

*نفس النمط العام*

---

## 📌 العملية 8: النشر وفتح التسجيل (Publish & Open Registration)

### 1. اسم العملية
`event.publish`

### 2. خطوات التنفيذ

```
[1] Validate publishable
    ├── All required fields complete
    ├── At least one ticket type
    ├── Valid dates
    └── Payment gateway configured (if paid)

[2] BEGIN DB TRANSACTION ─────────────────────────────
    │
    [3] Update status → published
    │
    [4] Set published_at
    │
    [5] If registration_opens_at is now or past:
    │   └── Set status → registration_open
    │
    [6] Create Revision (type: publish)
    │
COMMIT TRANSACTION ───────────────────────────────────

[7] Queue Jobs (HIGH PRIORITY)
    ├── IndexSearchJob
    ├── UpdateSitemapJob
    ├── GenerateCalendarLinksJob
    ├── NotifySubscribersJob
    ├── ScheduleRegistrationOpenJob (if future)
    ├── ScheduleEventRemindersJob
    └── SyncToExternalCalendarsJob
```

---

## 📌 العمليات الخاصة بالفعاليات

### 9. فتح التسجيل (Open Registration)

```http
POST /api/v1/events/{id}/open-registration
```

**خطوات التنفيذ:**
```
[1] Validate event is published

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → registration_open
    │
    [4] Set registration_opened_at
    │
COMMIT ───────────────────────────────────────────────

[5] Dispatch RegistrationOpened event

[6] Queue NotifyWaitlistJob (if any)
```

### 10. إغلاق التسجيل (Close Registration)

```http
POST /api/v1/events/{id}/close-registration
{
  "reason": "capacity_reached"
}
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status → registration_closed
    │
    [3] Store closure reason
    │
COMMIT ───────────────────────────────────────────────

[4] Dispatch RegistrationClosed event
```

### 11. التسجيل في الفعالية (Register for Event)

```http
POST /api/v1/events/{id}/register
{
  "attendee": {
    "name": "...",
    "email": "...",
    "phone": "..."
  },
  "ticket_type_id": "uuid",
  "quantity": 2,
  "custom_fields": {...}
}
```

**خطوات التنفيذ:**
```
[1] Validate registration open

[2] Validate capacity available

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Reserve tickets
    │   └── UPDATE event_tickets SET reserved = reserved + quantity
    │
    [5] Create Registration Record
    │   └── INSERT INTO event_registrations (event_id, attendee_id, ticket_type_id, quantity, status, ...)
    │
    [6] If paid event:
    │   └── Create payment intent (pending)
    │
    [7] If free event:
    │   └── Confirm registration immediately
    │
COMMIT ───────────────────────────────────────────────

[8] If paid:
    └── Return payment URL

[9] If free:
    ├── Generate tickets
    ├── Send confirmation email
    └── Generate calendar invite
```

### 12. تأكيد الدفع (Confirm Payment)

```
[Webhook from Payment Gateway]

[1] Validate payment signature

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update registration status → confirmed
    │
    [4] Convert reserved to sold
    │   └── UPDATE event_tickets SET sold = sold + quantity, reserved = reserved - quantity
    │
    [5] Generate unique ticket codes
    │
    [6] Create invoice
    │
COMMIT ───────────────────────────────────────────────

[7] Queue Jobs
    ├── SendConfirmationEmailJob
    ├── SendTicketsEmailJob
    ├── GenerateCalendarInviteJob
    └── UpdateCapacityDisplayJob
```

### 13. إلغاء التسجيل (Cancel Registration)

```http
POST /api/v1/events/{id}/registrations/{registration_id}/cancel
{
  "reason": "personal",
  "refund_requested": true
}
```

**خطوات التنفيذ:**
```
[1] Validate cancellation allowed
    └── Check event cancellation policy

[2] Calculate refund amount
    ├── Full refund if > X days before
    ├── Partial refund if > Y days before
    └── No refund if < Y days before

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Update registration status → cancelled
    │
    [5] Release tickets
    │   └── UPDATE event_tickets SET sold = sold - quantity
    │
    [6] Process refund (if applicable)
    │
    [7] Void ticket codes
    │
COMMIT ───────────────────────────────────────────────

[8] Queue Jobs
    ├── SendCancellationEmailJob
    ├── ProcessRefundJob
    └── NotifyWaitlistJob (offer spot)
```

### 14. إدارة قائمة الانتظار (Waitlist Management)

#### 14.1 الانضمام للقائمة
```http
POST /api/v1/events/{id}/waitlist
{
  "email": "...",
  "name": "...",
  "ticket_type_id": "uuid"
}
```

#### 14.2 ترقية من القائمة
```
[Triggered when spot available]

[1] Get next in waitlist

[2] Send offer email with deadline

[3] If accepted within deadline:
    └── Process registration

[4] If not accepted:
    └── Offer to next in list
```

### 15. إدارة التذاكر (Ticket Management)

#### 15.1 إضافة نوع تذكرة
```http
POST /api/v1/events/{id}/tickets
{
  "name": { "ar": "تذكرة مبكرة" },
  "price": 80,
  "currency": "SAR",
  "quantity": 100,
  "available_from": "2024-01-01",
  "available_until": "2024-02-01"
}
```

#### 15.2 تعديل سعر/كمية
```http
PUT /api/v1/events/{id}/tickets/{ticket_id}
{
  "price": 90,
  "quantity": 150
}
```

**ملاحظة**: لا يؤثر على التذاكر المباعة

#### 15.3 تعطيل نوع تذكرة
```http
POST /api/v1/events/{id}/tickets/{ticket_id}/disable
```

### 16. إدارة الجدول (Agenda/Sessions)

#### 16.1 إضافة جلسة
```http
POST /api/v1/events/{id}/sessions
{
  "start_at": "2024-03-15T11:00:00Z",
  "duration_minutes": 45,
  "speaker_id": "uuid",
  "room": "Main Hall",
  "translations": {
    "ar": { "title": "...", "description": "..." }
  }
}
```

#### 16.2 تعديل جلسة
```http
PUT /api/v1/events/{id}/sessions/{session_id}
```

#### 16.3 إلغاء جلسة
```http
POST /api/v1/events/{id}/sessions/{session_id}/cancel
{
  "reason": "speaker_unavailable",
  "notify_attendees": true
}
```

### 17. إدارة المتحدثين (Speaker Management)

#### 17.1 إضافة متحدث
```http
POST /api/v1/events/{id}/speakers
{
  "user_id": "uuid",
  "bio": { "ar": "..." },
  "photo_id": "media-uuid",
  "social_links": {...}
}
```

#### 17.2 إرسال دعوة للمتحدث
```http
POST /api/v1/events/{id}/speakers/{speaker_id}/invite
```

### 18. تسجيل الحضور (Check-in)

```http
POST /api/v1/events/{id}/check-in
{
  "ticket_code": "TICKET-XXXX-XXXX",
  "method": "qr_scan"
}
```

**خطوات التنفيذ:**
```
[1] Validate ticket code

[2] Check not already checked in

[3] BEGIN TRANSACTION ────────────────────────────────
    │
    [4] Update registration checked_in_at
    │
    [5] Log check-in
    │   └── INSERT INTO check_in_logs
    │
COMMIT ───────────────────────────────────────────────

[6] Return attendee details for badge
```

### 19. بدء الفعالية (Start Event)

```http
POST /api/v1/events/{id}/start
```

**خطوات التنفيذ:**
```
[1] Validate event time is now

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update status → ongoing
    │
    [4] Set actual_start_at
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs
    ├── StartLiveStreamJob (if virtual)
    ├── SendReminderToNoShowsJob
    └── EnableLiveQAJob
```

### 20. إنهاء الفعالية (End Event)

```http
POST /api/v1/events/{id}/end
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status → completed
    │
    [3] Set actual_end_at
    │
    [4] Calculate attendance stats
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs
    ├── SendThankYouEmailJob
    ├── SendSurveyJob
    ├── ProcessRecordingsJob
    ├── GenerateCertificatesJob
    └── CalculateEventMetricsJob
```

### 21. إرسال الشهادات (Send Certificates)

```http
POST /api/v1/events/{id}/send-certificates
{
  "template_id": "uuid",
  "recipients": "checked_in"
}
```

**خطوات التنفيذ:**
```
[1] Get eligible attendees

[2] For each attendee:
    │
    [3] Generate certificate PDF
    │
    [4] Store certificate
    │
    [5] Send email with certificate

[6] Log certificate issuance
```

### 22. نشر التسجيلات (Publish Recordings)

```http
POST /api/v1/events/{id}/recordings
{
  "session_id": "uuid",
  "video_url": "...",
  "access": "registered_only"
}
```

### 23. تأجيل الفعالية (Postpone Event)

```http
POST /api/v1/events/{id}/postpone
{
  "new_start_at": "2024-04-15T09:00:00Z",
  "new_end_at": "2024-04-15T17:00:00Z",
  "reason": "...",
  "notify_attendees": true,
  "offer_refund": true
}
```

**خطوات التنفيذ:**
```
[1] Validate new dates

[2] BEGIN TRANSACTION ────────────────────────────────
    │
    [3] Update event dates
    │
    [4] Update session times proportionally
    │
    [5] Log postponement
    │
COMMIT ───────────────────────────────────────────────

[6] Queue Jobs
    ├── NotifyAttendeesJob (with new dates)
    ├── UpdateCalendarInvitesJob
    ├── ProcessRefundRequestsJob (if offered)
    └── UpdateExternalCalendarsJob
```

### 24. إلغاء الفعالية (Cancel Event)

```http
POST /api/v1/events/{id}/cancel
{
  "reason": "...",
  "full_refund": true
}
```

**خطوات التنفيذ:**
```
[1] BEGIN TRANSACTION ────────────────────────────────
    │
    [2] Update status → cancelled
    │
    [3] Set cancelled_at, cancelled_reason
    │
    [4] Mark all tickets as cancelled
    │
COMMIT ───────────────────────────────────────────────

[5] Queue Jobs (CRITICAL)
    ├── ProcessAllRefundsJob
    ├── SendCancellationNoticesJob
    ├── CancelCalendarInvitesJob
    ├── RemoveFromListingsJob
    └── NotifySponsorsJob
```

---

## 🔄 Sequence Flow الكامل

```
┌─────────────────────────────────────────────────────────────────┐
│                      Event Lifecycle Flow                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Organizer        System          Attendee        Speaker       │
│    │                │                │              │            │
│    │── Create ─────▶│                │              │            │
│    │   + Tickets    │                │              │            │
│    │   + Sessions   │                │              │            │
│    │                │                │              │            │
│    │── Add Speaker ▶│── Invite ─────────────────────▶│            │
│    │                │                │              │            │
│    │── Publish ────▶│                │              │            │
│    │                │                │              │            │
│    │── Open Reg ───▶│                │              │            │
│    │                │       ◀── Register ──          │            │
│    │                │◀── Payment ────│              │            │
│    │                │── Tickets ────▶│              │            │
│    │                │                │              │            │
│    │                │── Reminder ───▶│              │            │
│    │                │                │              │            │
│    │── Start ──────▶│                │              │            │
│    │                │       ◀── Check-in ──          │            │
│    │                │                │    ◀─ Present│            │
│    │                │                │              │            │
│    │── End ────────▶│                │              │            │
│    │                │── Survey ─────▶│              │            │
│    │                │── Certificate ▶│              │            │
│    │                │                │              │            │
│    │── Archive ────▶│                │              │            │
│    │                │                │              │            │
└─────────────────────────────────────────────────────────────────┘
```

---

**نهاية تحليل عمليات الفعاليات**
