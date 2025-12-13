<?php

declare(strict_types=1);

namespace Modules\Notifications\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * NotificationTemplateTranslation Model - Stores localized template content.
 *
 * This model holds translated content for notification templates
 * including email subjects, bodies, and SMS content.
 *
 * @property string $id UUID primary key
 * @property string $template_id Foreign key to notification_templates
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string|null $email_subject Email subject line
 * @property string|null $email_body Email HTML body content
 * @property string|null $title Push notification title
 * @property string|null $body Push notification body
 * @property string|null $sms_body SMS message content
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read NotificationTemplate $template The parent template
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplateTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplateTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NotificationTemplateTranslation query()
 */
class NotificationTemplateTranslation extends Model
{
    use HasUuids;

    protected $table = 'notification_template_translations';

    protected $fillable = [
        'template_id',
        'locale',
        'email_subject',
        'email_body',
        'title',
        'body',
        'sms_body',
    ];

    /**
     * Get the template that owns this translation.
     *
     * @return BelongsTo<NotificationTemplate, NotificationTemplateTranslation>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class, 'template_id');
    }
}
