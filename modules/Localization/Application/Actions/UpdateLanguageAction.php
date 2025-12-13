<?php

declare(strict_types=1);

namespace Modules\Localization\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Localization\Domain\DTO\LanguageData;
use Modules\Localization\Domain\Models\Language;
use Modules\Localization\Domain\Repositories\LanguageRepository;

final class UpdateLanguageAction extends Action
{
    public function __construct(
        private readonly LanguageRepository $repository
    ) {}

    public function execute(Language $language, LanguageData $data): Language
    {
        if ($data->is_default && !$language->is_default) {
            Language::where('is_default', true)->update(['is_default' => false]);
        }

        $this->repository->update($language->id, [
            'name' => $data->name,
            'native_name' => $data->native_name ?? $data->name,
            'direction' => $data->direction,
            'is_active' => $data->is_active,
            'is_default' => $data->is_default,
            'sort_order' => $data->sort_order,
        ]);

        return $language->fresh();
    }
}
