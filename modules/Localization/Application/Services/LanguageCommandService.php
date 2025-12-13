<?php

declare(strict_types=1);

namespace Modules\Localization\Application\Services;

use Modules\Localization\Application\Actions\CreateLanguageAction;
use Modules\Localization\Application\Actions\DeleteLanguageAction;
use Modules\Localization\Application\Actions\SetDefaultLanguageAction;
use Modules\Localization\Application\Actions\UpdateLanguageAction;
use Modules\Localization\Domain\DTO\LanguageData;
use Modules\Localization\Domain\Models\Language;
use Modules\Localization\Domain\Repositories\LanguageRepository;

/**
 * Language Command Service.
 */
final class LanguageCommandService
{
    public function __construct(
        private readonly CreateLanguageAction $createAction,
        private readonly UpdateLanguageAction $updateAction,
        private readonly DeleteLanguageAction $deleteAction,
        private readonly SetDefaultLanguageAction $setDefaultAction,
        private readonly LanguageRepository $repository,
    ) {}

    public function create(LanguageData $data): Language
    {
        return $this->createAction->execute($data);
    }

    public function update(Language $language, LanguageData $data): Language
    {
        return $this->updateAction->execute($language, $data);
    }

    public function delete(Language $language): bool
    {
        return $this->deleteAction->execute($language);
    }

    public function setDefault(Language $language): Language
    {
        return $this->setDefaultAction->execute($language);
    }

    public function activate(Language $language): Language
    {
        $this->repository->update($language->id, ['is_active' => true]);

        return $language->fresh();
    }

    public function deactivate(Language $language): Language
    {
        if ($language->is_default) {
            return $language;
        }

        $this->repository->update($language->id, ['is_active' => false]);

        return $language->fresh();
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            $this->repository->update($id, ['sort_order' => $index]);
        }
    }
}
