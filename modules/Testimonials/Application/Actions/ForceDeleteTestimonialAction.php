<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Testimonials\Domain\Models\Testimonial;

/**
 * Force Delete Testimonial Action.
 *
 * Permanently deletes a testimonial.
 *
 * @package Modules\Testimonials\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ForceDeleteTestimonialAction extends Action
{
    /**
     * Execute the force delete action.
     *
     * @param string $id The testimonial ID
     *
     * @return bool True if deletion was successful
     */
    public function execute(string $id): bool
    {
        $testimonial = Testimonial::withTrashed()->find($id);
        return $testimonial?->forceDelete() ?? false;
    }
}
