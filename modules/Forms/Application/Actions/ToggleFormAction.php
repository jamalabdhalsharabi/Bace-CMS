<?php

declare(strict_types=1);

namespace Modules\Forms\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Forms\Domain\Models\Form;
use Modules\Forms\Domain\Repositories\FormRepository;

final class ToggleFormAction extends Action
{
    public function __construct(
        private readonly FormRepository $repository
    ) {}

    public function activate(Form $form): Form
    {
        $this->repository->update($form->id, ['is_active' => true]);

        return $form->fresh();
    }

    public function deactivate(Form $form): Form
    {
        $this->repository->update($form->id, ['is_active' => false]);

        return $form->fresh();
    }
}
