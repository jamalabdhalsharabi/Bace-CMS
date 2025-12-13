<?php

declare(strict_types=1);

namespace Modules\Seo\Application\Services;

use Modules\Seo\Application\Actions\CreateRedirectAction;
use Modules\Seo\Application\Actions\DeleteRedirectAction;
use Modules\Seo\Application\Actions\LogPageViewAction;
use Modules\Seo\Application\Actions\UpdateSeoMetaAction;
use Modules\Seo\Domain\Models\PageView;
use Modules\Seo\Domain\Models\Redirect;
use Modules\Seo\Domain\Models\SeoMeta;

final class SeoCommandService
{
    public function __construct(
        private readonly UpdateSeoMetaAction $updateMetaAction,
        private readonly CreateRedirectAction $createRedirectAction,
        private readonly DeleteRedirectAction $deleteRedirectAction,
        private readonly LogPageViewAction $logPageViewAction,
    ) {}

    public function updateMeta(string $entityType, string $entityId, array $data): SeoMeta
    {
        return $this->updateMetaAction->execute($entityType, $entityId, $data);
    }

    public function createRedirect(array $data): Redirect
    {
        return $this->createRedirectAction->execute($data);
    }

    public function deleteRedirect(Redirect $redirect): bool
    {
        return $this->deleteRedirectAction->execute($redirect);
    }

    public function logPageView(array $data): PageView
    {
        return $this->logPageViewAction->execute($data);
    }

    public function cleanOldPageViews(int $days = 90): int
    {
        return PageView::where('viewed_at', '<', now()->subDays($days))->delete();
    }
}
