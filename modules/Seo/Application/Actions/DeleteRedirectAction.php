<?php

declare(strict_types=1);

namespace Modules\Seo\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Seo\Domain\Models\Redirect;
use Modules\Seo\Domain\Repositories\RedirectRepository;

final class DeleteRedirectAction extends Action
{
    public function __construct(
        private readonly RedirectRepository $repository
    ) {}

    public function execute(Redirect $redirect): bool
    {
        return $this->repository->delete($redirect->id);
    }
}
