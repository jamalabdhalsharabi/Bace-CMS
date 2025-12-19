<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Testimonials\Domain\Models\Testimonial;

/**
 * Restore Testimonial Action.
 *
 * Restores a soft-deleted testimonial.
 *
 * @package Modules\Testimonials\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class RestoreTestimonialAction extends Action
{
    /**
     * Execute the restore action.
     *
     * @param string $id The testimonial ID
     *
     * @return Testimonial|null The restored testimonial
     */
    public function execute(string $id): ?Testimonial
    {
        $testimonial = Testimonial::withTrashed()->find($id);
        $testimonial?->restore();
        return $testimonial;
    }
}
