<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Page;

use Illuminate\Support\Str;
use Modules\Content\Domain\DTO\PageData;
use Modules\Content\Domain\Models\Page;
use Modules\Content\Domain\Repositories\PageRepository;
use Modules\Core\Application\Actions\Action;

final class CreatePageAction extends Action
{
    public function __construct(
        private readonly PageRepository $repository
    ) {}

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
