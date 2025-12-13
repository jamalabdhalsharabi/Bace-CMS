<?php

declare(strict_types=1);

namespace Modules\Localization\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Language
 *
 * Eloquent model representing a system language
 * with localization settings and default handling.
 *
 * @package Modules\Localization\Domain\Models
 *
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string $native_name
 * @property string $direction
 * @property string|null $flag
 * @property bool $is_default
 * @property bool $is_active
 * @property int $ordering
 */
class Language extends Model
{
    use HasUuids;

    protected $table = 'languages';

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'direction',
        'flag',
        'is_default',
        'is_active',
        'ordering',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'ordering' => 'integer',
    ];

    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    public function isLtr(): bool
    {
        return $this->direction === 'ltr';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('ordering');
    }

    public static function getDefault(): ?self
    {
        return static::where('is_default', true)->first();
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('code', $code)->first();
    }

    public static function getActiveCodes(): array
    {
        return static::active()->pluck('code')->toArray();
    }

    public function setAsDefault(): self
    {
        static::where('is_default', true)->update(['is_default' => false]);
        $this->update(['is_default' => true]);

        return $this;
    }
}
