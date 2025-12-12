<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use HasUuids;

    protected $table = 'form_fields';

    protected $fillable = [
        'form_id',
        'name',
        'label',
        'type',
        'placeholder',
        'default_value',
        'options',
        'validation_rules',
        'is_required',
        'ordering',
        'conditions',
    ];

    protected $casts = [
        'label' => 'array',
        'placeholder' => 'array',
        'options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'ordering' => 'integer',
        'conditions' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function getLabelAttribute($value): string
    {
        $labels = json_decode($value, true) ?? [];
        return $labels[app()->getLocale()] ?? $labels['en'] ?? $this->name;
    }

    public function getPlaceholderAttribute($value): ?string
    {
        if (!$value) return null;
        $placeholders = json_decode($value, true) ?? [];
        return $placeholders[app()->getLocale()] ?? $placeholders['en'] ?? null;
    }

    public function getValidationRulesArray(): array
    {
        $rules = $this->validation_rules ?? [];

        if ($this->is_required && !in_array('required', $rules)) {
            array_unshift($rules, 'required');
        }

        return $rules;
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, ['select', 'radio', 'checkbox']);
    }

    public function isFileField(): bool
    {
        return $this->type === 'file';
    }
}
