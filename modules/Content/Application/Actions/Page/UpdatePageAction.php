<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Page;

use Illuminate\Support\Str;
use Modules\Content\Domain\DTO\PageData;
use Modules\Content\Domain\Models\Page;
use Modules\Content\Domain\Repositories\PageRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Update Page Action.
 *
 * Handles updating existing CMS pages with multi-language support.
 *
 * @package Modules\Content\Application\Actions\Page
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UpdatePageAction extends Action
{
    /**
     * Create a new UpdatePageAction instance.
     *
     * @param PageRepository $repository The page repository for data operations
     */
    public function __construct(
        private readonly PageRepository $repository
    ) {}

    /**
     * Execute the page update action.
     *
     * Updates page data and translations.
     *
     * @param Page $page The page instance to update
     * @param PageData $data The validated page data transfer object
     * 
     * @return Page The updated page with translations loaded
     * 
     * @throws \Exception When update fails
     */
    public function execute(Page $page, PageData $data): Page
    {
        return $this->transaction(function () use ($page, $data) {
            $this->repository->update($page->id, [
                'template' => $data->template ?? $page->template,
                'featured_image_id' => $data->featured_image_id ?? $page->featured_image_id,
                'parent_id' => $data->parent_id ?? $page->parent_id,
                'sort_order' => $data->sort_order,
                'show_in_menu' => $data->show_in_menu,
                'meta' => $data->meta ?? $page->meta,
                'updated_by' => $this->userId(),
            ]);

            foreach ($data->translations as $locale => $trans) {
                $page->translations()->updateOrCreate(
                    ['locale' => $locale],
                    [
                        'title' => $trans['title'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                        'content' => $trans['content'] ?? null,
                        'meta_title' => $trans['meta_title'] ?? null,
                        'meta_description' => $trans['meta_description'] ?? null,
                    ]
                );
            }

            return $page->fresh(['translations']);
        });
    }
}
