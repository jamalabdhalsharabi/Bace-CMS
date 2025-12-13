<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FormFieldTranslation Model - Stores localized content for form fields.
 *
 * This model holds translated labels, placeholders, help text, and error
 * messages for form fields in each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $field_id Foreign key to form_fields table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $label Translated field label
 * @property string|null $placeholder Translated placeholder text
 * @property string|null $help_text Help text for the field
 * @property string|null $error_message Custom validation error message
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read FormField $field The parent form field
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormFieldTranslation query()
 */
class FormFieldTranslation extends Model
{
    use HasUuids;

    protected $table = 'form_field_translations';

    protected $fillable = [
        'field_id',
        'locale',
        'label',
        'placeholder',
        'help_text',
        'error_message',
    ];

    /**
     * Get the form field that owns this translation.
     *
     * @return BelongsTo<FormField, FormFieldTranslation>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }
}
