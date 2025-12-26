<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * FormSubmission Model - Stores form submission data.
 *
 * This model captures user form submissions with field data,
 * tracking information, assignment, and processing workflow.
 *
 * @property string $id UUID primary key
 * @property string $form_id Foreign key to forms table
 * @property string $status Status (pending, new, opened, in_progress, on_hold, completed, spam, archived)
 * @property bool $is_spam Whether flagged as spam
 * @property float|null $spam_score Spam detection score (0-100)
 * @property string|null $assigned_to UUID of assigned user
 * @property \Carbon\Carbon|null $assigned_at When submission was assigned
 * @property string|null $user_id UUID of submitting user (if authenticated)
 * @property string|null $ip_address Submitter's IP address
 * @property string|null $user_agent Submitter's browser user agent
 * @property string|null $source_url Page URL where form was submitted
 * @property string|null $referrer HTTP referrer URL
 * @property string|null $utm_source UTM source parameter
 * @property string|null $utm_medium UTM medium parameter
 * @property string|null $utm_campaign UTM campaign parameter
 * @property string|null $locale Submission language locale
 * @property array $data Submitted field values as JSON
 * @property string|null $tracking_code Unique tracking code
 * @property \Carbon\Carbon|null $opened_at When submission was first opened
 * @property string|null $opened_by UUID of user who opened
 * @property \Carbon\Carbon|null $completed_at When submission was completed
 * @property string|null $completed_by UUID of user who completed
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read Form $form Parent form
 * @property-read \App\Models\User|null $user Submitting user
 * @property-read \App\Models\User|null $assignee Assigned user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SubmissionFieldValue> $fieldValues Individual field values
 * @property-read \Illuminate\Database\Eloquent\Collection<int, SubmissionAttachment> $attachments File attachments
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission pending() Filter pending submissions
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission notSpam() Exclude spam
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission forForm(string $formId) Filter by form
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission query()
 */
class FormSubmission extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'form_submissions';

    protected $fillable = [
        'form_id',
        'status',
        'is_spam',
        'spam_score',
        'assigned_to',
        'assigned_at',
        'user_id',
        'ip_address',
        'user_agent',
        'source_url',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'locale',
        'data',
        'tracking_code',
        'opened_at',
        'opened_by',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'is_spam' => 'boolean',
        'spam_score' => 'decimal:2',
        'data' => 'array',
        'assigned_at' => 'datetime',
        'opened_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the parent form.
     *
     * @return BelongsTo<Form, FormSubmission>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get the submitting user.
     *
     * @return BelongsTo<\App\Models\User, FormSubmission>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Get the assigned user.
     *
     * @return BelongsTo<\App\Models\User, FormSubmission>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'assigned_to');
    }

    /**
     * Get individual field values.
     *
     * @return HasMany<SubmissionFieldValue>
     */
    public function fieldValues(): HasMany
    {
        return $this->hasMany(SubmissionFieldValue::class, 'submission_id');
    }

    /**
     * Get file attachments.
     *
     * @return HasMany<SubmissionAttachment>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(SubmissionAttachment::class, 'submission_id');
    }

    /**
     * Get a specific field value from the submission.
     *
     * @param string $fieldName The field name
     * @return mixed The field value or null
     */
    public function getValue(string $fieldName): mixed
    {
        return $this->data[$fieldName] ?? null;
    }

    /**
     * Mark submission as opened.
     *
     * @param string|null $userId UUID of user opening
     * @return self Returns self for method chaining
     */
    public function markAsOpened(?string $userId = null): self
    {
        if (!$this->opened_at) {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
                'opened_by' => $userId ?? request()->user()?->id,
            ]);
        }
        return $this;
    }

    /**
     * Mark submission as spam.
     *
     * @return self Returns self for method chaining
     */
    public function markAsSpam(): self
    {
        $this->update(['status' => 'spam', 'is_spam' => true]);
        return $this;
    }

    /**
     * Mark submission as completed.
     *
     * @param string|null $userId UUID of completing user
     * @return self Returns self for method chaining
     */
    public function markAsCompleted(?string $userId = null): self
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $userId ?? request()->user()?->id,
        ]);
        return $this;
    }

    /**
     * Assign submission to a user.
     *
     * @param string $userId UUID of user to assign
     * @return self Returns self for method chaining
     */
    public function assignTo(string $userId): self
    {
        $this->update([
            'assigned_to' => $userId,
            'assigned_at' => now(),
            'status' => 'in_progress',
        ]);
        return $this;
    }

    /**
     * Scope to filter pending submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder<FormSubmission> $query
     * @return \Illuminate\Database\Eloquent\Builder<FormSubmission>
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to exclude spam submissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder<FormSubmission> $query
     * @return \Illuminate\Database\Eloquent\Builder<FormSubmission>
     */
    public function scopeNotSpam($query)
    {
        return $query->where('is_spam', false);
    }

    /**
     * Scope to filter submissions by form.
     *
     * @param \Illuminate\Database\Eloquent\Builder<FormSubmission> $query
     * @param string $formId The form UUID
     * @return \Illuminate\Database\Eloquent\Builder<FormSubmission>
     */
    public function scopeForForm($query, string $formId)
    {
        return $query->where('form_id', $formId);
    }
}
