<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Services;

use Modules\Testimonials\Application\Actions\ApproveTestimonialAction;
use Modules\Testimonials\Application\Actions\CreateTestimonialAction;
use Modules\Testimonials\Application\Actions\DeleteTestimonialAction;
use Modules\Testimonials\Application\Actions\UpdateTestimonialAction;
use Modules\Testimonials\Domain\Models\Testimonial;

final class TestimonialCommandService
{
    public function __construct(
        private readonly CreateTestimonialAction $createAction,
        private readonly UpdateTestimonialAction $updateAction,
        private readonly DeleteTestimonialAction $deleteAction,
        private readonly ApproveTestimonialAction $approveAction,
    ) {}

    public function create(array $data): Testimonial
    {
        return $this->createAction->execute($data);
    }

    public function update(Testimonial $testimonial, array $data): Testimonial
    {
        return $this->updateAction->execute($testimonial, $data);
    }

    public function delete(Testimonial $testimonial): bool
    {
        return $this->deleteAction->execute($testimonial);
    }

    public function approve(Testimonial $testimonial): Testimonial
    {
        return $this->approveAction->execute($testimonial);
    }

    public function reject(Testimonial $testimonial): Testimonial
    {
        return $this->approveAction->reject($testimonial);
    }
}
