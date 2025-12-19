<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Testimonials\Domain\Models\Testimonial;

/**
 * Reorder Testimonials Action.
 *
 * Updates the sort order of testimonials.
 *
 * @package Modules\Testimonials\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ReorderTestimonialsAction extends Action
{
    /**
     * Execute the reorder action.
     *
     * @param array<int, string> $order Array of testimonial IDs in desired order
     *
     * @return void
     */
    public function execute(array $order): void
    {
        foreach ($order as $index => $id) {
            Testimonial::where('id', $id)->update(['sort_order' => $index]);
        }
    }
}
