<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Page;

use Illuminate\Support\Str;
use Modules\Content\Domain\DTO\PageData;
use Modules\Content\Domain\Models\Page;
use Modules\Content\Domain\Repositories\PageRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Create Page Action.
 *
 * Handles creation of new CMS pages with multi-language support
 * and hierarchical structure.
 *
 * @package Modules\Content\Application\Actions\Page
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CreatePageAction extends Action
{
    /**
     * Create a new CreatePageAction instance.
     *
     * @param PageRepository $repository The page repository for data operations
     */
    public function __construct(
        private readonly PageRepository $repository
    ) {}

    /**
     * Execute the page creation action.
     *
     * Creates a new page with translations and sets up hierarchical structure.
     *
     * @param PageData $data The validated page data transfer object
     * 
     * @return Page The newly created page with translations loaded
     * 
     * @throws \Exception When page creation fails
     */
    public function execute(PageData $data): Page
    {
        return $this->transaction(function () use ($data) {
            $page = $this->repository->create([
                'status' => $data->status,
                'template' => $data->template,
                'featured_image_id' => $data->featured_image_id,
                'parent_id' => $data->parent_id,
                'sort_order' => $data->sort_order,
                'show_in_menu' => $data->show_in_menu,
                'meta' => $data->meta,
                'created_by' => $this->userId(),
            ]);

            foreach ($data->translations as $locale => $trans) {
                $page->translations()->create([
                    'locale' => $locale,
                    'title' => $trans['title'],
                    'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                    'content' => $trans['content'] ?? null,
                    'meta_title' => $trans['meta_title'] ?? null,
                    'meta_description' => $trans['meta_description'] ?? null,
                ]);
            }

            return $page->fresh(['translations']);
        });
    }
}
