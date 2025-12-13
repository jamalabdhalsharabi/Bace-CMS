<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SubmissionFieldValue Model - Stores individual field values for submissions.
 *
 * This model holds the value of each form field for a specific submission,
 * allowing for normalized storage and querying of submission data.
 *
 * @property string $id UUID primary key
 * @property string $submission_id Foreign key to form_submissions table
 * @property string $field_id Foreign key to form_fields table
 * @property string|null $value The submitted value
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read FormSubmission $submission The parent submission
 * @property-read FormField $field The form field definition
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SubmissionFieldValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubmissionFieldValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubmissionFieldValue query()
 */
class SubmissionFieldValue extends Model
{
    use HasUuids;

    protected $table = 'submission_field_values';

    protected $fillable = [
        'submission_id',
        'field_id',
        'value',
    ];

    /**
     * Get the parent submission.
     *
     * @return BelongsTo<FormSubmission, SubmissionFieldValue>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'submission_id');
    }

    /**
     * Get the form field definition.
     *
     * @return BelongsTo<FormField, SubmissionFieldValue>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }
}
