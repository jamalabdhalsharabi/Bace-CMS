<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Form Model - Represents dynamic forms with configurable fields.
 *
 * This model manages form definitions with customizable fields,
 * submission handling, email notifications, and CAPTCHA support.
 *
 * @property string $id UUID primary key
 * @property string $slug Unique URL-friendly identifier
 * @property string $type Form type (contact, newsletter, survey, application)
 * @property bool $is_active Whether form accepts submissions
 * @property bool $requires_captcha Whether CAPTCHA is required
 * @property bool $requires_auth Whether authentication is required
 * @property int|null $rate_limit Submissions per IP per hour
 * @property string|null $notification_emails Comma-separated notification emails
 * @property bool $send_confirmation Whether to send confirmation email
 * @property string|null $confirmation_template_id FK to notification_templates
 * @property string|null $success_redirect URL to redirect after submission
 * @property bool $store_submissions Whether to store submissions in DB
 * @property string $created_by UUID of user who created the form
 * @property array|null $settings Additional form settings as JSON
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FormField> $fields Form fields
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FormSubmission> $submissions Form submissions
 * @property-read \App\Models\User $creator User who created the form
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Form active() Filter active forms
 * @method static \Illuminate\Database\Eloquent\Builder|Form ofType(string $type) Filter by form type
 * @method static \Illuminate\Database\Eloquent\Builder|Form newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form query()
 */
class Form extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'forms';

    protected $fillable = [
        'slug',
        'type',
        'is_active',
        'requires_captcha',
        'requires_auth',
        'rate_limit',
        'notification_emails',
        'send_confirmation',
        'confirmation_template_id',
        'success_redirect',
        'store_submissions',
        'created_by',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_captcha' => 'boolean',
        'requires_auth' => 'boolean',
        'rate_limit' => 'integer',
        'send_confirmation' => 'boolean',
        'store_submissions' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the user who created this form.
     *
     * @return BelongsTo<\App\Models\User, Form>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Get form fields ordered by position.
     *
     * @return HasMany<FormField>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('ordering');
    }

    /**
     * Get all form submissions.
     *
     * @return HasMany<FormSubmission>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    /**
     * Scope to filter only active forms.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Form> $query
     * @return \Illuminate\Database\Eloquent\Builder<Form>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter forms by type.
     *
     * @param \Illuminate\Database\Eloquent\Builder<Form> $query
     * @param string $type The form type
     * @return \Illuminate\Database\Eloquent\Builder<Form>
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Find a form by its slug.
     *
     * @param string $slug The form slug
     * @return self|null The form or null
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Get the total submission count.
     *
     * @return int Number of submissions
     */
    public function getSubmissionCount(): int
    {
        return $this->submissions()->count();
    }

    /**
     * Get the pending submission count.
     *
     * @return int Number of pending submissions
     */
    public function getPendingCount(): int
    {
        return $this->submissions()->where('status', 'pending')->count();
    }
}
