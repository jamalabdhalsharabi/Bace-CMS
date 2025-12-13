<?php

declare(strict_types=1);

namespace Modules\Localization\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Localization\Contracts\LanguageServiceContract;
use Modules\Localization\Domain\Models\Language;

/**
 * Class LanguageService
 *
 * Service class for managing languages and translations
 * including CRUD, file management, and synchronization.
 *
 * @package Modules\Localization\Services
 */
class LanguageService implements LanguageServiceContract
{
    /**
     * Get all languages.
     *
     * @return Collection Collection of Language models
     */
    public function all(): Collection
    {
        return $this->cached('all', fn () => Language::ordered()->get());
    }

    /**
     * Get all active languages.
     *
     * @return Collection Collection of active Language models
     */
    public function getActive(): Collection
    {
        return $this->cached('active', fn () => Language::active()->ordered()->get());
    }

    /**
     * Find a language by its UUID.
     *
     * @param string $id The language UUID
     *
     * @return Language|null The found language or null
     */
    public function find(string $id): ?Language
    {
        return Language::find($id);
    }

    /**
     * Find a language by its code.
     *
     * @param string $code The language code (e.g., 'en', 'ar')
     *
     * @return Language|null The found language or null
     */
    public function findByCode(string $code): ?Language
    {
        return $this->cached("code.{$code}", fn () => Language::findByCode($code));
    }

    /**
     * Get the default language.
     *
     * @return Language|null The default language or null
     */
    public function getDefault(): ?Language
    {
        return $this->cached('default', fn () => Language::getDefault());
    }

    /**
     * Create a new language.
     *
     * @param array $data Language data
     *
     * @return Language The created language
     */
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

    /**
     * Update an existing language.
     *
     * @param Language $language The language to update
     * @param array $data Updated data
     *
     * @return Language The updated language
     */
    public function update(Language $language, array $data): Language
    {
        $language->update($data);

        if (isset($data['is_default']) && $data['is_default']) {
            $language->setAsDefault();
        }

        $this->clearCache();

        return $language->fresh();
    }

    /**
     * Delete a language.
     *
     * @param Language $language The language to delete
     *
     * @return bool True if successful
     *
     * @throws \RuntimeException If trying to delete default language
     */
    public function delete(Language $language): bool
    {
        if ($language->is_default) {
            throw new \RuntimeException('Cannot delete default language.');
        }

        $result = $language->delete();
        $this->clearCache();

        return $result;
    }

    /**
     * Set a language as the default.
     *
     * @param Language $language The language to set as default
     *
     * @return Language The updated language
     */
    public function setDefault(Language $language): Language
    {
        $language->setAsDefault();
        $this->clearCache();

        return $language->fresh();
    }

    /**
     * Reorder languages by their IDs.
     *
     * @param array $order Array of language UUIDs in order
     *
     * @return void
     */
    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            Language::where('id', $id)->update(['ordering' => $index + 1]);
        }

        $this->clearCache();
    }

    /**
     * Get cached data or execute callback.
     *
     * @param string $key Cache key
     * @param callable $callback Callback to execute if not cached
     *
     * @return mixed Cached or fresh data
     */
    protected function cached(string $key, callable $callback): mixed
    {
        if (!config('localization.cache.enabled', true)) {
            return $callback();
        }

        $prefix = config('localization.cache.prefix', 'lang_');
        $ttl = config('localization.cache.ttl', 3600);

        return Cache::remember($prefix . $key, $ttl, $callback);
    }

    /**
     * Clear all language-related cache.
     *
     * @return void
     */
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

    /**
     * Activate a language.
     *
     * @param Language $language The language to activate
     *
     * @return Language The activated language
     */
    public function activate(Language $language): Language
    {
        $language->update(['is_active' => true]);
        $this->clearCache();
        return $language->fresh();
    }

    /**
     * Deactivate a language.
     *
     * @param Language $language The language to deactivate
     *
     * @return Language The deactivated language
     *
     * @throws \RuntimeException If trying to deactivate default language
     */
    public function deactivate(Language $language): Language
    {
        if ($language->is_default) {
            throw new \RuntimeException('Cannot deactivate default language.');
        }
        $language->update(['is_active' => false]);
        $this->clearCache();
        return $language->fresh();
    }

    /**
     * Get translations from a file.
     *
     * @param string $locale The locale code
     * @param string $group The translation group
     *
     * @return array Translation key-value pairs
     */
    public function getTranslationFile(string $locale, string $group): array
    {
        $path = lang_path("{$locale}/{$group}.php");
        return file_exists($path) ? include $path : [];
    }

    /**
     * Update a translation file.
     *
     * @param string $locale The locale code
     * @param string $group The translation group
     * @param array $translations New translations
     *
     * @return bool True if successful
     */
    public function updateTranslationFile(string $locale, string $group, array $translations): bool
    {
        $path = lang_path("{$locale}/{$group}.php");
        $content = "<?php\n\nreturn " . var_export($translations, true) . ";\n";
        return file_put_contents($path, $content) !== false;
    }

    /**
     * Import translations from array data.
     *
     * @param string $locale The locale code
     * @param array $data Translation data by group
     *
     * @return array Import results
     */
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

    /**
     * Export all translations for a locale.
     *
     * @param string $locale The locale code
     *
     * @return array All translations by group
     */
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

    /**
     * Sync translations from one locale to another.
     *
     * @param string $fromLocale Source locale
     * @param string $toLocale Target locale
     *
     * @return int Number of groups synced
     */
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

    /**
     * Auto-translate using external provider.
     *
     * @param string $fromLocale Source locale
     * @param string $toLocale Target locale
     * @param string $provider Translation provider
     *
     * @return array Job status
     */
    public function autoTranslate(string $fromLocale, string $toLocale, string $provider = 'google'): array
    {
        // Implementation depends on translation provider
        return ['status' => 'queued', 'provider' => $provider];
    }

    /**
     * Get translation progress for a locale.
     *
     * @param string $locale The locale code
     *
     * @return array Progress statistics
     */
    public function getContentTranslationProgress(string $locale): array
    {
        return [
            'locale' => $locale,
            'total' => 0,
            'translated' => 0,
            'percentage' => 0,
        ];
    }

    /**
     * Get missing translations for a locale.
     *
     * @param string $locale The locale code
     *
     * @return array Missing translation keys
     */
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

    /**
     * Set fallback locale for a language.
     *
     * @param Language $language The language
     * @param string $fallbackCode Fallback locale code
     *
     * @return Language The updated language
     */
    public function setFallback(Language $language, string $fallbackCode): Language
    {
        $language->update(['fallback_locale' => $fallbackCode]);
        $this->clearCache();
        return $language->fresh();
    }
}
