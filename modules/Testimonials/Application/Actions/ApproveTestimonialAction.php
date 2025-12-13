<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Testimonials\Domain\Models\Testimonial;
use Modules\Testimonials\Domain\Repositories\TestimonialRepository;

final class ApproveTestimonialAction extends Action
{
    public function __construct(
        private readonly TestimonialRepository $repository
    ) {}

    public function execute(Testimonial $testimonial): Testimonial
    {
        $this->repository->update($testimonial->id, ['status' => 'approved']);
        return $testimonial->fresh();
    }

    public function reject(Testimonial $testimonial): Testimonial
    {
        $this->repository->update($testimonial->id, ['status' => 'rejected']);
        return $testimonial->fresh();
    }
}
