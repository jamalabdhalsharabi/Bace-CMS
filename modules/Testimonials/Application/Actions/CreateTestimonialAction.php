<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Testimonials\Domain\Models\Testimonial;
use Modules\Testimonials\Domain\Repositories\TestimonialRepository;

final class CreateTestimonialAction extends Action
{
    public function __construct(
        private readonly TestimonialRepository $repository
    ) {}

    public function execute(array $data): Testimonial
    {
        return $this->transaction(function () use ($data) {
            $testimonial = $this->repository->create([
                'author_name' => $data['author_name'],
                'author_title' => $data['author_title'] ?? null,
                'author_company' => $data['author_company'] ?? null,
                'author_image_id' => $data['author_image_id'] ?? null,
                'rating' => $data['rating'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'is_featured' => $data['is_featured'] ?? false,
                'sort_order' => $data['sort_order'] ?? 0,
                'created_by' => $this->userId(),
            ]);

            foreach ($data['translations'] ?? [] as $locale => $trans) {
                $testimonial->translations()->create([
                    'locale' => $locale,
                    'content' => $trans['content'],
                ]);
            }

            return $testimonial->fresh(['translations']);
        });
    }
}
