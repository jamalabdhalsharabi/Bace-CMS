<?php

declare(strict_types=1);

namespace Modules\Seo\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    use HasUuids;

    protected $table = 'redirects';

    protected $fillable = [
        'source_path',
        'target_path',
        'status_code',
        'is_active',
        'is_regex',
        'hits_count',
        'last_hit_at',
        'notes',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'is_active' => 'boolean',
        'is_regex' => 'boolean',
        'hits_count' => 'integer',
        'last_hit_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        
        static::saved(fn() => Cache::forget('redirects_active'));
        static::deleted(fn() => Cache::forget('redirects_active'));
    }

    public function recordHit(): void
    {
        $this->increment('hits_count');
        $this->update(['last_hit_at' => now()]);
    }

    public function matches(string $path): bool
    {
        if ($this->is_regex) {
            return (bool) preg_match('#' . $this->source_path . '#', $path);
        }
        
        return $this->source_path === $path || $this->source_path === '/' . ltrim($path, '/');
    }

    public function getTargetUrl(string $originalPath): string
    {
        if ($this->is_regex) {
            return preg_replace('#' . $this->source_path . '#', $this->target_path, $originalPath);
        }
        
        return $this->target_path;
    }

    public static function findRedirect(string $path): ?self
    {
        $redirects = Cache::remember('redirects_active', 3600, fn() =>
            static::where('is_active', true)->get()
        );

        return $redirects->first(fn($r) => $r->matches($path));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
