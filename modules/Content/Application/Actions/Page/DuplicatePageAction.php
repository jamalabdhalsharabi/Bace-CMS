<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Page;

use Modules\Content\Domain\Models\Page;
use Modules\Core\Application\Actions\Action;

/**
 * Duplicate Page Action.
 *
 * Creates a complete copy of an existing page including all translations.
 *
 * @package Modules\Content\Application\Actions\Page
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class DuplicatePageAction extends Action
{
    /**
     * Execute the page duplication action.
     *
     * @param Page $page The source page to duplicate
     * 
     * @return Page The newly created duplicate page
     * 
     * @throws \Exception When duplication fails
     */
    public function execute(Page $page): Page
    {
        return $this->transaction(function () use ($page) {
            $clone = $page->replicate(['status', 'published_at']);
            $clone->status = 'draft';
            $clone->created_by = $this->userId();
            $clone->save();

            foreach ($page->translations as $trans) {
                $clone->translations()->create([
                    'locale' => $trans->locale,
                    'title' => $trans->title . ' (Copy)',
                    'slug' => $trans->slug . '-copy-' . time(),
                    'content' => $trans->content,
                    'meta_title' => $trans->meta_title,
                    'meta_description' => $trans->meta_description,
                ]);
            }

            return $clone->fresh(['translations']);
        });
    }
}
