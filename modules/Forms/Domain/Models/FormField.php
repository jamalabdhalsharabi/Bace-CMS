<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\HasTranslations;

/**
 * FormField Model - Defines individual form input fields.
 *
 * This model represents configurable form fields with localized labels,
 * validation rules, conditional visibility, and various input types.
 *
 * @property string $id UUID primary key
 * @property string $form_id Foreign key to forms table
 * @property string $name Field name/identifier (max 50 chars)
 * @property string $type Input type (text, email, select, file, etc.)
 * @property string|null $default_value Default field value
 * @property array|null $options Options for select/radio/checkbox as JSON
 * @property string|null $allowed_extensions Allowed file extensions
 * @property int|null $max_file_size Maximum file size in KB
 * @property array|null $validation_rules Laravel validation rules as JSON
 * @property bool $is_required Whether field is mandatory
 * @property int $ordering Display order in form
 * @property string $width Field width (full, half, third)
 * @property string|null $css_class Additional CSS classes
 * @property array|null $conditions Conditional visibility rules as JSON
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Form $form Parent form
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FormFieldTranslation> $translations All translations
 * @property-read FormFieldTranslation|null $translation Current locale translation
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FormField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormField query()
 */
class FormField extends Model
{
    use HasUuids;
    use HasTranslations;

    /**
     * Translatable attributes (Astrotomic format).
     *
     * @var array<string>
     */
    public array $translatedAttributes = ['label', 'placeholder', 'help_text', 'error_message'];

    /**
     * Custom foreign key for translations.
     *
     * @var string
     */
    public string $translationForeignKey = 'field_id';

    protected $table = 'form_fields';

    protected $fillable = [
        'form_id',
        'name',
        'type',
        'default_value',
        'options',
        'allowed_extensions',
        'max_file_size',
        'validation_rules',
        'is_required',
        'ordering',
        'width',
        'css_class',
        'conditions',
    ];

    protected $casts = [
        'options' => 'array',
        'max_file_size' => 'integer',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'ordering' => 'integer',
        'conditions' => 'array',
    ];

    /**
     * Get the parent form.
     *
     * @return BelongsTo<Form, FormField>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get validation rules array with required prepended if needed.
     *
     * @return array<int, string> Validation rules
     */
    public function getValidationRulesArray(): array
    {
        $rules = $this->validation_rules ?? [];

        if ($this->is_required && !in_array('required', $rules)) {
            array_unshift($rules, 'required');
        }

        return $rules;
    }

    /**
     * Check if field type supports options.
     *
     * @return bool True for select, radio, checkbox types
     */
    public function hasOptions(): bool
    {
        return in_array($this->type, ['select', 'radio', 'checkbox']);
    }

    /**
     * Check if this is a file upload field.
     *
     * @return bool True if type is 'file'
     */
    public function isFileField(): bool
    {
        return $this->type === 'file';
    }
}
