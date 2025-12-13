<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Testimonials\Domain\Models\Testimonial;
use Modules\Testimonials\Domain\Repositories\TestimonialRepository;

final class DeleteTestimonialAction extends Action
{
    public function __construct(
        private readonly TestimonialRepository $repository
    ) {}

    public function execute(Testimonial $testimonial): bool
    {
        $testimonial->translations()->delete();
        return $this->repository->delete($testimonial->id);
    }
}
