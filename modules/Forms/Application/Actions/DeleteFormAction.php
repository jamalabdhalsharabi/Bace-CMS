<?php

declare(strict_types=1);

namespace Modules\Forms\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Forms\Domain\Models\Form;
use Modules\Forms\Domain\Repositories\FormRepository;

final class DeleteFormAction extends Action
{
    public function __construct(
        private readonly FormRepository $repository
    ) {}

    public function execute(Form $form): bool
    {
        return $this->repository->delete($form->id);
    }
}
