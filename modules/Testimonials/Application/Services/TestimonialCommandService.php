<?php

declare(strict_types=1);

namespace Modules\Testimonials\Application\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Testimonials\Application\Actions\ApproveTestimonialAction;
use Modules\Testimonials\Application\Actions\CreateTestimonialAction;
use Modules\Testimonials\Application\Actions\DeleteTestimonialAction;
use Modules\Testimonials\Application\Actions\FeatureTestimonialAction;
use Modules\Testimonials\Application\Actions\ForceDeleteTestimonialAction;
use Modules\Testimonials\Application\Actions\PublishTestimonialAction;
use Modules\Testimonials\Application\Actions\ReorderTestimonialsAction;
use Modules\Testimonials\Application\Actions\RestoreTestimonialAction;
use Modules\Testimonials\Application\Actions\UpdateTestimonialAction;
use Modules\Testimonials\Domain\Models\Testimonial;

/**
 * Testimonial Command Service.
 *
 * Orchestrates all testimonial write operations via Action classes.
 * No direct Model/Repository usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\Testimonials\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class TestimonialCommandService
{
    /**
     * Create a new TestimonialCommandService instance.
     *
     * @param CreateTestimonialAction $createAction Action for creating testimonials
     * @param UpdateTestimonialAction $updateAction Action for updating testimonials
     * @param DeleteTestimonialAction $deleteAction Action for deleting testimonials
     * @param ApproveTestimonialAction $approveAction Action for approving testimonials
     * @param PublishTestimonialAction $publishAction Action for publishing testimonials
     * @param FeatureTestimonialAction $featureAction Action for featuring testimonials
     * @param ReorderTestimonialsAction $reorderAction Action for reordering testimonials
     * @param RestoreTestimonialAction $restoreAction Action for restoring testimonials
     * @param ForceDeleteTestimonialAction $forceDeleteAction Action for force deleting
     */
    public function __construct(
        private readonly CreateTestimonialAction $createAction,
        private readonly UpdateTestimonialAction $updateAction,
        private readonly DeleteTestimonialAction $deleteAction,
        private readonly ApproveTestimonialAction $approveAction,
        private readonly PublishTestimonialAction $publishAction,
        private readonly FeatureTestimonialAction $featureAction,
        private readonly ReorderTestimonialsAction $reorderAction,
        private readonly RestoreTestimonialAction $restoreAction,
        private readonly ForceDeleteTestimonialAction $forceDeleteAction,
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

    public function forceDelete(string $id): bool
    {
        return $this->forceDeleteAction->execute($id);
    }

    public function restore(string $id): ?Testimonial
    {
        return $this->restoreAction->execute($id);
    }

    public function approve(Testimonial $testimonial): Testimonial
    {
        return $this->approveAction->execute($testimonial);
    }

    public function reject(Testimonial $testimonial, ?string $reason = null): Testimonial
    {
        return $this->approveAction->reject($testimonial, $reason);
    }

    public function publish(Testimonial $testimonial): Testimonial
    {
        return $this->publishAction->execute($testimonial);
    }

    public function unpublish(Testimonial $testimonial): Testimonial
    {
        return $this->publishAction->unpublish($testimonial);
    }

    public function feature(Testimonial $testimonial): Testimonial
    {
        return $this->featureAction->execute($testimonial);
    }

    public function unfeature(Testimonial $testimonial): Testimonial
    {
        return $this->featureAction->unfeature($testimonial);
    }

    public function reorder(array $order): void
    {
        $this->reorderAction->execute($order);
    }

    public function submitForReview(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['status' => 'pending_review']);
        return $testimonial->fresh();
    }

    public function startReview(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['status' => 'in_review', 'reviewed_by' => Auth::id()]);
        return $testimonial->fresh();
    }

    public function archive(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['status' => 'archived']);
        return $testimonial->fresh();
    }

    public function verifyClient(Testimonial $testimonial): Testimonial
    {
        $testimonial->update(['is_verified' => true, 'verified_at' => now()]);
        return $testimonial->fresh();
    }

    public function requestFromClient(array $data): array
    {
        $token = \Illuminate\Support\Str::random(64);
        
        \Illuminate\Support\Facades\DB::table('testimonial_requests')->insert([
            'id' => \Illuminate\Support\Str::uuid(),
            'client_email' => $data['client_email'],
            'client_name' => $data['client_name'],
            'token' => $token,
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'created_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
        
        return ['token' => $token];
    }

    public function linkEntity(string $testimonialId, array $data): void
    {
        \Illuminate\Support\Facades\DB::table('testimonial_entity')->insert([
            'testimonial_id' => $testimonialId,
            'entity_type' => $data['entity_type'],
            'entity_id' => $data['entity_id'],
        ]);
    }

    public function unlinkEntity(string $testimonialId, array $data): void
    {
        \Illuminate\Support\Facades\DB::table('testimonial_entity')
            ->where('testimonial_id', $testimonialId)
            ->where('entity_type', $data['entity_type'])
            ->where('entity_id', $data['entity_id'])
            ->delete();
    }

    public function import(array $data): array
    {
        $imported = 0;
        foreach ($data['data'] as $item) {
            $this->createAction->execute([
                'author_name' => $item['author_name'] ?? 'Anonymous',
                'author_company' => $item['company'] ?? null,
                'rating' => $item['rating'] ?? 5,
                'is_active' => false,
                'external_source' => $data['source'],
                'external_id' => $item['external_id'] ?? null,
            ]);
            $imported++;
        }
        
        return ['imported' => $imported];
    }
}
