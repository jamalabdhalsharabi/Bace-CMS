<?php

declare(strict_types=1);

namespace Modules\Localization\Application\Services;

use Modules\Localization\Application\Actions\CreateLanguageAction;
use Modules\Localization\Application\Actions\DeleteLanguageAction;
use Modules\Localization\Application\Actions\ReorderLanguagesAction;
use Modules\Localization\Application\Actions\SetDefaultLanguageAction;
use Modules\Localization\Application\Actions\ToggleLanguageAction;
use Modules\Localization\Application\Actions\UpdateLanguageAction;
use Modules\Localization\Domain\DTO\LanguageData;
use Modules\Localization\Domain\Models\Language;

/**
 * Language Command Service.
 *
 * Orchestrates all write operations for languages via Action classes.
 * No direct Model/Repository usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\Localization\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class LanguageCommandService
{
    /**
     * Create a new LanguageCommandService instance.
     *
     * @param CreateLanguageAction $createAction Action for creating languages
     * @param UpdateLanguageAction $updateAction Action for updating languages
     * @param DeleteLanguageAction $deleteAction Action for deleting languages
     * @param SetDefaultLanguageAction $setDefaultAction Action for setting default language
     * @param ToggleLanguageAction $toggleAction Action for activating/deactivating languages
     * @param ReorderLanguagesAction $reorderAction Action for reordering languages
     */
    public function __construct(
        private readonly CreateLanguageAction $createAction,
        private readonly UpdateLanguageAction $updateAction,
        private readonly DeleteLanguageAction $deleteAction,
        private readonly SetDefaultLanguageAction $setDefaultAction,
        private readonly ToggleLanguageAction $toggleAction,
        private readonly ReorderLanguagesAction $reorderAction,
    ) {}

    /**
     * Create a new language.
     *
     * @param LanguageData $data The language data DTO
     *
     * @return Language The created language
     */
    public function create(LanguageData $data): Language
    {
        return $this->createAction->execute($data);
    }

    /**
     * Update an existing language.
     *
     * @param Language $language The language to update
     * @param LanguageData $data The updated language data
     *
     * @return Language The updated language
     */
    public function update(Language $language, LanguageData $data): Language
    {
        return $this->updateAction->execute($language, $data);
    }

    /**
     * Delete a language.
     *
     * @param Language $language The language to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Language $language): bool
    {
        return $this->deleteAction->execute($language);
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
        return $this->setDefaultAction->execute($language);
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
        return $this->toggleAction->activate($language);
    }

    /**
     * Deactivate a language.
     *
     * @param Language $language The language to deactivate
     *
     * @return Language The deactivated language
     */
    public function deactivate(Language $language): Language
    {
        return $this->toggleAction->deactivate($language);
    }

    /**
     * Reorder languages.
     *
     * @param array<int, string> $order Array of language IDs in desired order
     *
     * @return void
     */
    public function reorder(array $order): void
    {
        $this->reorderAction->execute($order);
    }
}
