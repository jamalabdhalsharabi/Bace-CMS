<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

/**
 * HasTranslations Trait - Wrapper around Astrotomic Translatable with Auto Eager Loading.
 *
 * This trait combines Astrotomic's translation capabilities with automatic
 * eager loading to prevent N+1 queries.
 *
 * Usage:
 * 1. Add `use HasTranslations;` to your model
 * 2. Define `public array $translatedAttributes = ['title', 'description'];`
 * 3. Create Translation model (e.g., ArticleTranslation)
 *
 * Features:
 * - Auto-eager loads translations (current locale + fallback)
 * - Full Astrotomic Translatable features
 * - Easy CRUD for translations
 * - Fallback locale support
 *
 * @package Modules\Core\Traits
 *
 * @property-read \Illuminate\Database\Eloquent\Collection $translations
 * @property-read Model|null $translation
 *
 * @method static Builder withAllTranslations()
 * @method static Builder withoutAutoloadTranslations()
 * @method static Builder listsTranslations(string $attribute)
 */
trait HasTranslations
{
    use Translatable;

    /**
     * Boot the trait - Add global scope for auto eager loading.
     *
     * @return void
     */
    public static function bootHasTranslations(): void
    {
        // Auto eager load translations with current locale and fallback
        static::addGlobalScope('translations', function (Builder $builder) {
            $locale = App::getLocale();
            $fallback = config('app.fallback_locale', 'en');
            $locales = array_unique([$locale, $fallback]);

            $builder->with(['translations' => function ($query) use ($locales) {
                $query->whereIn('locale', $locales);
            }]);
        });
    }

    /**
     * Scope: Load all translations (not just current + fallback).
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithAllTranslations(Builder $query): Builder
    {
        return $query->withoutGlobalScope('translations')->with('translations');
    }

    /**
     * Scope: Disable auto-loading of translations.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithoutAutoloadTranslations(Builder $query): Builder
    {
        return $query->withoutGlobalScope('translations');
    }

    /**
     * Scope: Filter by translated attribute value.
     *
     * @param Builder $query
     * @param string $attribute
     * @param mixed $value
     * @param string|null $locale
     * @return Builder
     */
    public function scopeWhereTranslation(Builder $query, string $attribute, mixed $value, ?string $locale = null): Builder
    {
        $locale = $locale ?? App::getLocale();

        return $query->whereTranslation($attribute, $value, $locale);
    }

    /**
     * Scope: Search in translated attribute.
     *
     * @param Builder $query
     * @param string $attribute
     * @param string $value
     * @param string|null $locale
     * @return Builder
     */
    public function scopeWhereTranslationLike(Builder $query, string $attribute, string $value, ?string $locale = null): Builder
    {
        $locale = $locale ?? App::getLocale();

        return $query->whereTranslationLike($attribute, "%{$value}%", $locale);
    }

    /**
     * Scope: Order by translated attribute.
     *
     * @param Builder $query
     * @param string $attribute
     * @param string $direction
     * @param string|null $locale
     * @return Builder
     */
    public function scopeOrderByTranslation(Builder $query, string $attribute, string $direction = 'asc', ?string $locale = null): Builder
    {
        $locale = $locale ?? App::getLocale();

        return $query->orderByTranslation($attribute, $direction, $locale);
    }

    /**
     * Create with translations in one call.
     *
     * Usage:
     * Article::createWithTranslations([
     *     'status' => 'draft',
     * ], [
     *     'en' => ['title' => 'Hello', 'content' => '...'],
     *     'ar' => ['title' => 'مرحبا', 'content' => '...'],
     * ]);
     *
     * @param array $attributes Model attributes
     * @param array $translations Translations keyed by locale
     * @return static
     */
    public static function createWithTranslations(array $attributes, array $translations): static
    {
        $model = static::create($attributes);

        foreach ($translations as $locale => $translationData) {
            $model->translateOrNew($locale)->fill($translationData);
        }

        $model->save();
        $model->load('translations');

        return $model;
    }

    /**
     * Update with translations in one call.
     *
     * @param array $attributes Model attributes
     * @param array $translations Translations keyed by locale
     * @return bool
     */
    public function updateWithTranslations(array $attributes, array $translations): bool
    {
        $this->fill($attributes);

        foreach ($translations as $locale => $translationData) {
            $this->translateOrNew($locale)->fill($translationData);
        }

        $saved = $this->save();
        $this->load('translations');

        return $saved;
    }

    /**
     * Get translations formatted for API response.
     *
     * @return array<string, array>
     */
    public function getTranslationsArray(): array
    {
        $result = [];

        foreach ($this->translations as $translation) {
            $result[$translation->locale] = $translation->toArray();
        }

        return $result;
    }

    /**
     * Sync translations - removes unlisted locales.
     *
     * @param array $translations Translations keyed by locale
     * @return static
     */
    public function syncTranslations(array $translations): static
    {
        // Delete translations not in the list
        $this->translations()
            ->whereNotIn('locale', array_keys($translations))
            ->delete();

        // Update or create translations
        foreach ($translations as $locale => $translationData) {
            $this->translateOrNew($locale)->fill($translationData);
        }

        $this->save();
        $this->load('translations');

        return $this;
    }

    /**
     * Clone model with all translations.
     *
     * @return static
     */
    public function replicateWithTranslations(): static
    {
        $clone = $this->replicate();
        $clone->push();

        foreach ($this->translations as $translation) {
            $clonedTranslation = $translation->replicate();
            $clonedTranslation->{$this->getTranslationRelationKey()} = $clone->id;
            $clonedTranslation->save();
        }

        $clone->load('translations');

        return $clone;
    }
}
