<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Testimonials\Domain\Models\Testimonial;

/**
 * Feature Testimonial Action.
 *
 * Toggles the featured status of a testimonial.
 *
 * @package Modules\Testimonials\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class FeatureTestimonialAction extends Action
{
    /**
     * Feature a testimonial.
     *
     * @param Testimonial $testimonial The testimonial to feature
     *
     * @return Testimonial The featured testimonial
     */
    public function execute(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['is_featured' => true]);
        return $testimonial->fresh();
    }

    /**
     * Unfeature a testimonial.
     *
     * @param Testimonial $testimonial The testimonial to unfeature
     *
     * @return Testimonial The unfeatured testimonial
     */
    public function unfeature(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['is_featured' => false]);
        return $testimonial->fresh();
    }
}
