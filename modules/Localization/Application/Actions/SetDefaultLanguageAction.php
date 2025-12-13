<?php

declare(strict_types=1);

namespace Modules\Localization\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Localization\Domain\Models\Language;
use Modules\Localization\Domain\Repositories\LanguageRepository;

final class SetDefaultLanguageAction extends Action
{
    public function __construct(
        private readonly LanguageRepository $repository
    ) {}

    public function execute(Language $language): Language
    {
        Language::where('is_default', true)->update(['is_default' => false]);
        $this->repository->update($language->id, ['is_default' => true]);

        return $language->fresh();
    }
}
