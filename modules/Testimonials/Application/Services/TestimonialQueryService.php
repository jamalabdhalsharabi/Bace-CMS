<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Testimonials\Domain\Models\Testimonial;
use Modules\Testimonials\Domain\Repositories\TestimonialRepository;

final class TestimonialQueryService
{
    public function __construct(
        private readonly TestimonialRepository $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function getApproved(): Collection
    {
        return $this->repository->getApproved();
    }

    public function getFeatured(): Collection
    {
        return $this->repository->getFeatured();
    }

    public function findById(string $id): ?Testimonial
    {
        return $this->repository->find($id);
    }
}
