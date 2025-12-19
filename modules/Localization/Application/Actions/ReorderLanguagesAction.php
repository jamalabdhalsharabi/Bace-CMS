<?php

declare(strict_types=1);

namespace Modules\Localization\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Localization\Domain\Models\Language;

/**
 * Reorder Languages Action.
 *
 * Updates the sort order of languages.
 *
 * @package Modules\Localization\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ReorderLanguagesAction extends Action
{
    /**
     * Execute the reorder action.
     *
     * @param array<int, string> $order Array of language IDs in desired order
     *
     * @return void
     */
    public function execute(array $order): void
    {
        foreach ($order as $index => $id) {
            Language::where('id', $id)->update(['sort_order' => $index]);
        }
    }
}
