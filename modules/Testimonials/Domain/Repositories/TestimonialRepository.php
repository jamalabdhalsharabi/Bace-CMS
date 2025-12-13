<?php

declare(strict_types=1);

namespace Modules\Testimonials\Domain\Repositories;

use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Testimonials\Domain\Models\Testimonial;

class TestimonialRepository extends BaseRepository
{
    public function __construct(Testimonial $model)
    {
        parent::__construct($model);
    }

    public function getApproved(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('status', 'approved')
            ->with('translations')
            ->orderBy('sort_order')
            ->get();
    }

    public function getFeatured(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->where('status', 'approved')
            ->where('is_featured', true)
            ->with('translations')
            ->orderBy('sort_order')
            ->get();
    }
}
