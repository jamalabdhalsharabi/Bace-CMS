<?php

declare(strict_types=1);

namespace Modules\Seo\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Seo\Domain\Models\Redirect;
use Modules\Seo\Domain\Repositories\RedirectRepository;

final class CreateRedirectAction extends Action
{
    public function __construct(
        private readonly RedirectRepository $repository
    ) {}

    public function execute(array $data): Redirect
    {
        return $this->repository->create([
            'source_url' => $data['source_url'],
            'target_url' => $data['target_url'],
            'status_code' => $data['status_code'] ?? 301,
            'is_active' => $data['is_active'] ?? true,
            'priority' => $data['priority'] ?? 0,
        ]);
    }
}
