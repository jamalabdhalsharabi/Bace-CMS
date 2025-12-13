<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Page;

use Illuminate\Support\Str;
use Modules\Content\Domain\DTO\PageData;
use Modules\Content\Domain\Models\Page;
use Modules\Content\Domain\Repositories\PageRepository;
use Modules\Core\Application\Actions\Action;

final class UpdatePageAction extends Action
{
    public function __construct(
        private readonly PageRepository $repository
    ) {}

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
