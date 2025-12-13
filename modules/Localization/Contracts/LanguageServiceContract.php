<?php

declare(strict_types=1);

namespace Modules\Localization\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Localization\Domain\Models\Language;

/**
 * Interface LanguageServiceContract
 * 
 * Defines the contract for language and translation management services.
 * Handles CRUD, activation, translation files, sync, auto-translate,
 * content translation progress, and fallback configuration.
 * 
 * @package Modules\Localization\Contracts
 */
interface LanguageServiceContract
{
    /** @return Collection All languages */
    public function all(): Collection;

    /** @return Collection Active languages only */
    public function getActive(): Collection;

    /** @param string $id @return Language|null */
    public function find(string $id): ?Language;

    /** @param string $code Language code (e.g., 'en', 'ar') @return Language|null */
    public function findByCode(string $code): ?Language;

    /** @return Language|null The default language */
    public function getDefault(): ?Language;

    /** @param array $data @return Language */
    public function create(array $data): Language;

    /** @param Language $language @param array $data @return Language */
    public function update(Language $language, array $data): Language;

    /** @param Language $language @return bool */
    public function delete(Language $language): bool;

    /** @param Language $language @return Language */
    public function activate(Language $language): Language;

    /** @param Language $language @return Language */
    public function deactivate(Language $language): Language;

    /** @param Language $language @return Language */
    public function setDefault(Language $language): Language;

    /** @param array $order Array of language IDs @return void */
    public function reorder(array $order): void;

    /** @param string $locale @param string $group @return array */
    public function getTranslationFile(string $locale, string $group): array;

    /** @param string $locale @param string $group @param array $translations @return bool */
    public function updateTranslationFile(string $locale, string $group, array $translations): bool;

    /** @param string $locale @param array $data @return array */
    public function importTranslations(string $locale, array $data): array;

    /** @param string $locale @return array */
    public function exportTranslations(string $locale): array;

    /** @param string $fromLocale @param string $toLocale @return int Number of synced keys */
    public function syncTranslations(string $fromLocale, string $toLocale): int;

    /** @param string $fromLocale @param string $toLocale @param string $provider @return array */
    public function autoTranslate(string $fromLocale, string $toLocale, string $provider = 'google'): array;

    /** @param string $locale @return array Progress statistics */
    public function getContentTranslationProgress(string $locale): array;

    /** @param string $locale @return array Missing translation keys */
    public function getMissingTranslations(string $locale): array;

    /** @param Language $language @param string $fallbackCode @return Language */
    public function setFallback(Language $language, string $fallbackCode): Language;
}
