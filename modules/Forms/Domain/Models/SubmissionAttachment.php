<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SubmissionAttachment Model - Stores file attachments for form submissions.
 *
 * This model tracks files uploaded through form submissions, including
 * file metadata and optional linking to the Media module.
 *
 * @property string $id UUID primary key
 * @property string $submission_id Foreign key to form_submissions table
 * @property string $field_id Foreign key to form_fields table
 * @property string|null $media_id Foreign key to media table (optional)
 * @property string $filename Stored filename
 * @property string $original_filename Original uploaded filename
 * @property string $mime_type File MIME type
 * @property int $size File size in bytes
 * @property string $path Storage path
 * @property \Carbon\Carbon $created_at Record creation timestamp
 *
 * @property-read FormSubmission $submission The parent submission
 * @property-read FormField $field The form field definition
 * @property-read \Modules\Media\Domain\Models\Media|null $media Linked media record
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SubmissionAttachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubmissionAttachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubmissionAttachment query()
 */
class SubmissionAttachment extends Model
{
    use HasUuids;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $table = 'submission_attachments';

    protected $fillable = [
        'submission_id',
        'field_id',
        'media_id',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'path',
        'created_at',
    ];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the parent submission.
     *
     * @return BelongsTo<FormSubmission, SubmissionAttachment>
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'submission_id');
    }

    /**
     * Get the form field definition.
     *
     * @return BelongsTo<FormField, SubmissionAttachment>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }

    /**
     * Get the linked media record.
     *
     * @return BelongsTo<\Modules\Media\Domain\Models\Media, SubmissionAttachment>
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(\Modules\Media\Domain\Models\Media::class, 'media_id');
    }
}
