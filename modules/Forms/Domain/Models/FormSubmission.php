<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasUuids;

    protected $table = 'form_submissions';

    protected $fillable = [
        'form_id',
        'user_id',
        'data',
        'ip_address',
        'user_agent',
        'referrer',
        'status',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'processed_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function getValue(string $fieldName): mixed
    {
        return $this->data[$fieldName] ?? null;
    }

    public function markAsRead(): self
    {
        $this->update(['status' => 'read']);
        return $this;
    }

    public function markAsSpam(): self
    {
        $this->update(['status' => 'spam']);
        return $this;
    }

    public function markAsProcessed(?string $notes = null): self
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
            'notes' => $notes,
        ]);
        return $this;
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeNotSpam($query)
    {
        return $query->where('status', '!=', 'spam');
    }

    public function scopeForForm($query, string $formId)
    {
        return $query->where('form_id', $formId);
    }
}
