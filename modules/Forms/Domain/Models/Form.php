<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Form
 *
 * Eloquent model representing a dynamic form
 * with fields, submissions, and notifications.
 *
 * @package Modules\Forms\Domain\Models
 *
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property array $success_message
 * @property array $notification_emails
 * @property string|null $redirect_url
 * @property bool $is_active
 * @property bool $captcha_enabled
 * @property array|null $settings
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|FormField[] $fields
 * @property-read \Illuminate\Database\Eloquent\Collection|FormSubmission[] $submissions
 */
class Form extends Model
{
    use HasUuids;

    protected $table = 'forms';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'type',
        'success_message',
        'notification_emails',
        'redirect_url',
        'is_active',
        'captcha_enabled',
        'settings',
    ];

    protected $casts = [
        'success_message' => 'array',
        'notification_emails' => 'array',
        'is_active' => 'boolean',
        'captcha_enabled' => 'boolean',
        'settings' => 'array',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('ordering');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function getSuccessMessageAttribute($value): string
    {
        $messages = json_decode($value, true) ?? [];
        return $messages[app()->getLocale()] ?? $messages['en'] ?? 'Thank you for your submission!';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public function getSubmissionCount(): int
    {
        return $this->submissions()->count();
    }

    public function getUnreadCount(): int
    {
        return $this->submissions()->where('status', 'new')->count();
    }
}
