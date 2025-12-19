<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Testimonials\Domain\Models\Testimonial;

/**
 * Publish Testimonial Action.
 *
 * Publishes or unpublishes a testimonial.
 *
 * @package Modules\Testimonials\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PublishTestimonialAction extends Action
{
    /**
     * Publish a testimonial.
     *
     * @param Testimonial $testimonial The testimonial to publish
     *
     * @return Testimonial The published testimonial
     */
    public function execute(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['is_active' => true, 'published_at' => now()]);
        return $testimonial->fresh();
    }

    /**
     * Unpublish a testimonial.
     *
     * @param Testimonial $testimonial The testimonial to unpublish
     *
     * @return Testimonial The unpublished testimonial
     */
    public function unpublish(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['is_active' => false]);
        return $testimonial->fresh();
    }

    /**
     * Archive a testimonial.
     *
     * @param Testimonial $testimonial The testimonial to archive
     *
     * @return Testimonial The archived testimonial
     */
    public function archive(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['status' => 'archived']);
        return $testimonial->fresh();
    }
}
