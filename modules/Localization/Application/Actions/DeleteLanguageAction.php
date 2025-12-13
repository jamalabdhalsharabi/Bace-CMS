<?php

declare(strict_types=1);

namespace Modules\Localization\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Localization\Domain\Models\Language;
use Modules\Localization\Domain\Repositories\LanguageRepository;

final class DeleteLanguageAction extends Action
{
    public function __construct(
        private readonly LanguageRepository $repository
    ) {}

    public function execute(Language $language): bool
    {
        if ($language->is_default) {
            return false;
        }

        return $this->repository->delete($language->id);
    }
}
