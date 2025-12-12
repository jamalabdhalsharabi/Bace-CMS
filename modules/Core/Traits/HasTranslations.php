<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\App;

trait HasTranslations
{
    /**
     * Boot the trait.
     */
    public static function bootHasTranslations(): void
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'translations')) {
                $model->translations()->delete();
            }
        });
    }

    /**
     * Get all translations.
     */
    public function translations(): HasMany
    {
        return $this->hasMany($this->getTranslationModelClass());
    }

    /**
     * Get translation for current locale.
     */
    public function translation(): HasOne
    {
        return $this->hasOne($this->getTranslationModelClass())
            ->where('locale', $this->getLocale());
    }

    /**
     * Get translation for specific locale.
     */
    public function translateTo(string $locale): HasOne
    {
        return $this->hasOne($this->getTranslationModelClass())
            ->where('locale', $locale);
    }

    /**
     * Get translated attribute.
     */
    public function translate(string $attribute, ?string $locale = null, bool $fallback = true): ?string
    {
        $locale = $locale ?? $this->getLocale();
        
        $translation = $this->translations->firstWhere('locale', $locale);

        if ($translation && isset($translation->{$attribute})) {
            return $translation->{$attribute};
        }

        if ($fallback && $locale !== $this->getFallbackLocale()) {
            return $this->translate($attribute, $this->getFallbackLocale(), false);
        }

        return null;
    }

    /**
     * Get or create translation for locale.
     */
    public function getOrCreateTranslation(string $locale): mixed
    {
        $translation = $this->translations->firstWhere('locale', $locale);

        if (!$translation) {
            $translation = $this->translations()->create([
                'locale' => $locale,
            ]);
            $this->load('translations');
        }

        return $translation;
    }

    /**
     * Set translation for attribute.
     */
    public function setTranslation(string $attribute, string $value, ?string $locale = null): static
    {
        $locale = $locale ?? $this->getLocale();
        
        $this->translations()->updateOrCreate(
            ['locale' => $locale],
            [$attribute => $value]
        );

        $this->load('translations');

        return $this;
    }

    /**
     * Set multiple translations.
     */
    public function setTranslations(array $translations, ?string $locale = null): static
    {
        $locale = $locale ?? $this->getLocale();

        $this->translations()->updateOrCreate(
            ['locale' => $locale],
            $translations
        );

        $this->load('translations');

        return $this;
    }

    /**
     * Check if translation exists for locale.
     */
    public function hasTranslation(?string $locale = null): bool
    {
        $locale = $locale ?? $this->getLocale();
        return $this->translations->contains('locale', $locale);
    }

    /**
     * Get all available locales for this model.
     */
    public function getAvailableLocales(): array
    {
        return $this->translations->pluck('locale')->toArray();
    }

    /**
     * Delete translation for locale.
     */
    public function deleteTranslation(string $locale): bool
    {
        $deleted = $this->translations()->where('locale', $locale)->delete();
        $this->load('translations');
        return $deleted > 0;
    }

    /**
     * Get translation model class.
     */
    protected function getTranslationModelClass(): string
    {
        return $this->translationModel ?? static::class . 'Translation';
    }

    /**
     * Get current locale.
     */
    protected function getLocale(): string
    {
        return App::getLocale();
    }

    /**
     * Get fallback locale.
     */
    protected function getFallbackLocale(): string
    {
        return config('app.fallback_locale', 'en');
    }

    /**
     * Magic getter for translated attributes.
     */
    public function getAttribute($key)
    {
        // Check if it's a translatable attribute
        if ($this->isTranslatableAttribute($key)) {
            return $this->translate($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Check if attribute is translatable.
     */
    protected function isTranslatableAttribute(string $key): bool
    {
        return in_array($key, $this->translatable ?? []);
    }

    /**
     * Scope: With current translation.
     */
    public function scopeWithTranslation($query, ?string $locale = null)
    {
        $locale = $locale ?? $this->getLocale();

        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    /**
     * Scope: With all translations.
     */
    public function scopeWithTranslations($query)
    {
        return $query->with('translations');
    }
}
