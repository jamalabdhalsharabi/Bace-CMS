<?php

declare(strict_types=1);

namespace Modules\Localization\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Localization\Domain\Models\Language;

interface LanguageServiceContract
{
    // CRUD
    public function all(): Collection;
    public function getActive(): Collection;
    public function find(string $id): ?Language;
    public function findByCode(string $code): ?Language;
    public function getDefault(): ?Language;
    public function create(array $data): Language;
    public function update(Language $language, array $data): Language;
    public function delete(Language $language): bool;

    // Status
    public function activate(Language $language): Language;
    public function deactivate(Language $language): Language;
    public function setDefault(Language $language): Language;
    public function reorder(array $order): void;

    // Translations
    public function getTranslationFile(string $locale, string $group): array;
    public function updateTranslationFile(string $locale, string $group, array $translations): bool;
    public function importTranslations(string $locale, array $data): array;
    public function exportTranslations(string $locale): array;
    public function syncTranslations(string $fromLocale, string $toLocale): int;

    // Auto-translate
    public function autoTranslate(string $fromLocale, string $toLocale, string $provider = 'google'): array;

    // Content translations
    public function getContentTranslationProgress(string $locale): array;
    public function getMissingTranslations(string $locale): array;

    // Fallback
    public function setFallback(Language $language, string $fallbackCode): Language;
}
