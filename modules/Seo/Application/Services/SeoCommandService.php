<?php

declare(strict_types=1);

namespace Modules\Seo\Application\Services;

use Modules\Seo\Application\Actions\CleanOldPageViewsAction;
use Modules\Seo\Application\Actions\CreateRedirectAction;
use Modules\Seo\Application\Actions\DeleteRedirectAction;
use Modules\Seo\Application\Actions\LogPageViewAction;
use Modules\Seo\Application\Actions\UpdateSeoMetaAction;
use Modules\Seo\Domain\Models\PageView;
use Modules\Seo\Domain\Models\Redirect;
use Modules\Seo\Domain\Models\SeoMeta;

/**
 * SEO Command Service.
 *
 * Orchestrates all SEO write operations via Action classes.
 * Handles meta updates, redirects, and page view logging.
 *
 * @package Modules\Seo\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class SeoCommandService
{
    /**
     * Create a new SeoCommandService instance.
     *
     * @param UpdateSeoMetaAction $updateMetaAction Action for updating SEO meta
     * @param CreateRedirectAction $createRedirectAction Action for creating redirects
     * @param DeleteRedirectAction $deleteRedirectAction Action for deleting redirects
     * @param LogPageViewAction $logPageViewAction Action for logging page views
     * @param CleanOldPageViewsAction $cleanAction Action for cleaning old page views
     */
    public function __construct(
        private readonly UpdateSeoMetaAction $updateMetaAction,
        private readonly CreateRedirectAction $createRedirectAction,
        private readonly DeleteRedirectAction $deleteRedirectAction,
        private readonly LogPageViewAction $logPageViewAction,
        private readonly CleanOldPageViewsAction $cleanAction,
    ) {}

    /**
     * Update SEO meta for an entity.
     *
     * @param string $entityType The entity type
     * @param string $entityId The entity ID
     * @param array<string, mixed> $data The SEO data
     *
     * @return SeoMeta The updated SEO meta
     */
    public function updateMeta(string $entityType, string $entityId, array $data): SeoMeta
    {
        return $this->updateMetaAction->execute($entityType, $entityId, $data);
    }

    /**
     * Create a URL redirect.
     *
     * @param array<string, mixed> $data The redirect data
     *
     * @return Redirect The created redirect
     */
    public function createRedirect(array $data): Redirect
    {
        return $this->createRedirectAction->execute($data);
    }

    /**
     * Delete a URL redirect.
     *
     * @param Redirect $redirect The redirect to delete
     *
     * @return bool True if deletion was successful
     */
    public function deleteRedirect(Redirect $redirect): bool
    {
        return $this->deleteRedirectAction->execute($redirect);
    }

    /**
     * Log a page view.
     *
     * @param array<string, mixed> $data The page view data
     *
     * @return PageView The created page view record
     */
    public function logPageView(array $data): PageView
    {
        return $this->logPageViewAction->execute($data);
    }

    /**
     * Clean old page view records.
     *
     * @param int $days Number of days to keep records
     *
     * @return int Number of deleted records
     */
    public function cleanOldPageViews(int $days = 90): int
    {
        return $this->cleanAction->execute($days);
    }
}
