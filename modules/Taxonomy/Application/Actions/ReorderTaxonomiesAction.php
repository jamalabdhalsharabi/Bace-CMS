<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Taxonomy\Domain\Models\Taxonomy;

/**
 * Reorder Taxonomies Action.
 *
 * Updates the sort order of taxonomies.
 *
 * @package Modules\Taxonomy\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ReorderTaxonomiesAction extends Action
{
    /**
     * Execute the reorder action.
     *
     * @param array<int, string> $order Array of taxonomy IDs in desired order
     *
     * @return void
     */
    public function execute(array $order): void
    {
        foreach ($order as $index => $id) {
            Taxonomy::where('id', $id)->update(['sort_order' => $index]);
        }
    }
}
