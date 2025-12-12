<?php

declare(strict_types=1);

namespace Modules\Localization\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Localization\Contracts\LanguageServiceContract;
use Modules\Localization\Domain\Models\Language;

class LanguageService implements LanguageServiceContract
{
    public function all(): Collection
    {
        return $this->cached('all', fn () => Language::ordered()->get());
    }

    public function getActive(): Collection
    {
        return $this->cached('active', fn () => Language::active()->ordered()->get());
    }

    public function find(string $id): ?Language
    {
        return Language::find($id);
    }

    public function findByCode(string $code): ?Language
    {
        return $this->cached("code.{$code}", fn () => Language::findByCode($code));
    }

    public function getDefault(): ?Language
    {
        return $this->cached('default', fn () => Language::getDefault());
    }

    public function create(array $data): Language
    {
        $language = Language::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'native_name' => $data['native_name'] ?? $data['name'],
            'direction' => $data['direction'] ?? 'ltr',
            'flag' => $data['flag'] ?? null,
            'is_default' => $data['is_default'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'ordering' => $data['ordering'] ?? Language::max('ordering') + 1,
        ]);

        if ($language->is_default) {
            $language->setAsDefault();
        }

        $this->clearCache();

        return $language;
    }

    public function update(Language $language, array $data): Language
    {
        $language->update($data);

        if (isset($data['is_default']) && $data['is_default']) {
            $language->setAsDefault();
        }

        $this->clearCache();

        return $language->fresh();
    }

    public function delete(Language $language): bool
    {
        if ($language->is_default) {
            throw new \RuntimeException('Cannot delete default language.');
        }

        $result = $language->delete();
        $this->clearCache();

        return $result;
    }

    public function setDefault(Language $language): Language
    {
        $language->setAsDefault();
        $this->clearCache();

        return $language->fresh();
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            Language::where('id', $id)->update(['ordering' => $index + 1]);
        }

        $this->clearCache();
    }

    protected function cached(string $key, callable $callback): mixed
    {
        if (!config('localization.cache.enabled', true)) {
            return $callback();
        }

        $prefix = config('localization.cache.prefix', 'lang_');
        $ttl = config('localization.cache.ttl', 3600);

        return Cache::remember($prefix . $key, $ttl, $callback);
    }

    public function clearCache(): void
    {
        $prefix = config('localization.cache.prefix', 'lang_');
        $keys = ['all', 'active', 'default'];

        foreach ($keys as $key) {
            Cache::forget($prefix . $key);
        }

        foreach (Language::pluck('code') as $code) {
            Cache::forget($prefix . "code.{$code}");
        }
    }

    public function activate(Language $language): Language
    {
        $language->update(['is_active' => true]);
        $this->clearCache();
        return $language->fresh();
    }

    public function deactivate(Language $language): Language
    {
        if ($language->is_default) {
            throw new \RuntimeException('Cannot deactivate default language.');
        }
        $language->update(['is_active' => false]);
        $this->clearCache();
        return $language->fresh();
    }

    public function getTranslationFile(string $locale, string $group): array
    {
        $path = lang_path("{$locale}/{$group}.php");
        return file_exists($path) ? include $path : [];
    }

    public function updateTranslationFile(string $locale, string $group, array $translations): bool
    {
        $path = lang_path("{$locale}/{$group}.php");
        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
        return file_put_contents($path, $content) !== false;
    }

    public function importTranslations(string $locale, array $data): array
    {
        $results = ['imported' => 0, 'errors' => []];
        foreach ($data as $group => $translations) {
            if ($this->updateTranslationFile($locale, $group, $translations)) {
                $results['imported']++;
            } else {
                $results['errors'][] = $group;
            }
        }
        return $results;
    }

    public function exportTranslations(string $locale): array
    {
        $path = lang_path($locale);
        $translations = [];
        if (is_dir($path)) {
            foreach (glob("{$path}/*.php") as $file) {
                $group = basename($file, '.php');
                $translations[$group] = include $file;
            }
        }
        return $translations;
    }

    public function syncTranslations(string $fromLocale, string $toLocale): int
    {
        $source = $this->exportTranslations($fromLocale);
        $count = 0;
        foreach ($source as $group => $translations) {
            $existing = $this->getTranslationFile($toLocale, $group);
            $merged = array_merge($translations, $existing);
            $this->updateTranslationFile($toLocale, $group, $merged);
            $count++;
        }
        return $count;
    }

    public function autoTranslate(string $fromLocale, string $toLocale, string $provider = 'google'): array
    {
        // Implementation depends on translation provider
        return ['status' => 'queued', 'provider' => $provider];
    }

    public function getContentTranslationProgress(string $locale): array
    {
        return [
            'locale' => $locale,
            'total' => 0,
            'translated' => 0,
            'percentage' => 0,
        ];
    }

    public function getMissingTranslations(string $locale): array
    {
        $default = $this->getDefault();
        if (!$default) return [];

        $source = $this->exportTranslations($default->code);
        $target = $this->exportTranslations($locale);
        $missing = [];

        foreach ($source as $group => $translations) {
            $targetGroup = $target[$group] ?? [];
            foreach ($translations as $key => $value) {
                if (!isset($targetGroup[$key])) {
                    $missing["{$group}.{$key}"] = $value;
                }
            }
        }
        return $missing;
    }

    public function setFallback(Language $language, string $fallbackCode): Language
    {
        $language->update(['fallback_locale' => $fallbackCode]);
        $this->clearCache();
        return $language->fresh();
    }
}
