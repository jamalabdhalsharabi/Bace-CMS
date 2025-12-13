<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Testimonials\Domain\Models\Testimonial;
use Modules\Testimonials\Domain\Repositories\TestimonialRepository;

final class UpdateTestimonialAction extends Action
{
    public function __construct(
        private readonly TestimonialRepository $repository
    ) {}

    public function execute(Testimonial $testimonial, array $data): Testimonial
    {
        return $this->transaction(function () use ($testimonial, $data) {
            $this->repository->update($testimonial->id, array_filter([
                'author_name' => $data['author_name'] ?? null,
                'author_title' => $data['author_title'] ?? null,
                'author_company' => $data['author_company'] ?? null,
                'author_image_id' => $data['author_image_id'] ?? null,
                'rating' => $data['rating'] ?? null,
                'is_featured' => $data['is_featured'] ?? null,
                'sort_order' => $data['sort_order'] ?? null,
                'updated_by' => $this->userId(),
            ], fn($v) => $v !== null));

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $testimonial->translations()->updateOrCreate(
                        ['locale' => $locale],
                        ['content' => $trans['content']]
                    );
                }
            }

            return $testimonial->fresh(['translations']);
        });
    }
}
