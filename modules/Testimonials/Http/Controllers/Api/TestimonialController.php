<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Testimonials\Application\Services\TestimonialCommandService;
use Modules\Testimonials\Application\Services\TestimonialQueryService;
use Modules\Testimonials\Domain\Models\Testimonial;
use Modules\Testimonials\Http\Resources\TestimonialResource;

class TestimonialController extends BaseController
{
    public function __construct(
        protected TestimonialQueryService $queryService,
        protected TestimonialCommandService $commandService
    ) {
    }
    /**
     * Display a paginated listing of active testimonials.
     *
     * @param Request $request The request with optional featured filter
     * @return JsonResponse Paginated list of testimonials
     */
    public function index(Request $request): JsonResponse
    {
        $query = Testimonial::with(['translation', 'avatar'])->active();
        if ($request->boolean('featured')) $query->featured();
        $testimonials = $query->orderBy('sort_order')->paginate($request->integer('per_page', 10));
        return $this->paginated(TestimonialResource::collection($testimonials)->resource);
    }

    /**
     * Display the specified testimonial.
     *
     * @param string $id The UUID of the testimonial
     * @return JsonResponse The testimonial or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $testimonial = Testimonial::with(['translations', 'avatar'])->find($id);
        return $testimonial ? $this->success(new TestimonialResource($testimonial)) : $this->notFound();
    }

    /**
     * Store a newly created testimonial.
     *
     * @param Request $request The request with testimonial data
     * @return JsonResponse The created testimonial (HTTP 201)
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_title' => 'nullable|string|max:255',
            'author_company' => 'nullable|string|max:255',
            'author_avatar_id' => 'nullable|uuid|exists:media,id',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_featured' => 'nullable|boolean',
            'translations' => 'required|array|min:1',
            'translations.*.content' => 'required|string',
        ]);

        $testimonial = DB::transaction(function () use ($data) {
            $t = Testimonial::create([
                'author_name' => $data['author_name'],
                'author_title' => $data['author_title'] ?? null,
                'author_company' => $data['author_company'] ?? null,
                'author_avatar_id' => $data['author_avatar_id'] ?? null,
                'rating' => $data['rating'] ?? 5,
                'is_featured' => $data['is_featured'] ?? false,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);
            foreach ($data['translations'] as $locale => $trans) {
                $t->translations()->create(['locale' => $locale, 'content' => $trans['content']]);
            }
            return $t->fresh(['translations']);
        });

        return $this->created(new TestimonialResource($testimonial));
    }

    /**
     * Update the specified testimonial.
     *
     * @param Request $request The request with updated data
     * @param string $id The UUID of the testimonial
     * @return JsonResponse The updated testimonial or 404 error
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update($request->all());
        return $this->success(new TestimonialResource($testimonial->fresh()));
    }

    /**
     * Delete the specified testimonial.
     */
    public function destroy(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->delete();
        return $this->success(null, 'Testimonial deleted');
    }

    /**
     * Force delete testimonial permanently.
     */
    public function forceDestroy(string $id): JsonResponse
    {
        $testimonial = Testimonial::withTrashed()->find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->forceDelete();
        return $this->success(null, 'Testimonial permanently deleted');
    }

    /**
     * Restore soft-deleted testimonial.
     */
    public function restore(string $id): JsonResponse
    {
        $testimonial = Testimonial::withTrashed()->find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->restore();
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Submit testimonial for review.
     */
    public function submitForReview(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update(['status' => 'pending_review']);
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Start reviewing the testimonial.
     */
    public function startReview(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update(['status' => 'in_review', 'reviewed_by' => auth()->id()]);
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Approve the testimonial.
     */
    public function approve(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => auth()->id()]);
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Reject the testimonial.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update(['status' => 'rejected', 'rejection_reason' => $request->reason]);
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Publish the testimonial.
     */
    public function publish(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update(['is_active' => true, 'published_at' => now()]);
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Unpublish the testimonial.
     */
    public function unpublish(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update(['is_active' => false]);
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Archive the testimonial.
     */
    public function archive(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update(['status' => 'archived']);
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Feature the testimonial.
     */
    public function feature(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update(['is_featured' => true]);
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Unfeature the testimonial.
     */
    public function unfeature(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update(['is_featured' => false]);
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Verify client identity.
     */
    public function verifyClient(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update(['is_verified' => true, 'verified_at' => now()]);
        return $this->success(new TestimonialResource($testimonial));
    }

    /**
     * Request testimonial from client.
     */
    public function requestTestimonial(Request $request): JsonResponse
    {
        $request->validate([
            'client_email' => 'required|email',
            'client_name' => 'required|string|max:255',
            'message' => 'nullable|string',
            'entity_type' => 'nullable|string',
            'entity_id' => 'nullable|uuid',
        ]);
        
        $token = Str::random(64);
        DB::table('testimonial_requests')->insert([
            'id' => Str::uuid(),
            'client_email' => $request->client_email,
            'client_name' => $request->client_name,
            'token' => $token,
            'entity_type' => $request->entity_type,
            'entity_id' => $request->entity_id,
            'created_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);
        
        return $this->success(['token' => $token], 'Testimonial request sent');
    }

    /**
     * Link testimonial to entity.
     */
    public function linkEntity(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'entity_type' => 'required|string|in:service,product,project',
            'entity_id' => 'required|uuid',
        ]);
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        
        DB::table('testimonial_entity')->insert([
            'testimonial_id' => $id,
            'entity_type' => $request->entity_type,
            'entity_id' => $request->entity_id,
        ]);
        
        return $this->success(null, 'Entity linked');
    }

    /**
     * Unlink testimonial from entity.
     */
    public function unlinkEntity(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'entity_type' => 'required|string',
            'entity_id' => 'required|uuid',
        ]);
        
        DB::table('testimonial_entity')
            ->where('testimonial_id', $id)
            ->where('entity_type', $request->entity_type)
            ->where('entity_id', $request->entity_id)
            ->delete();
        
        return $this->success(null, 'Entity unlinked');
    }

    /**
     * Reorder testimonials.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'uuid']);
        foreach ($request->order as $index => $id) {
            Testimonial::where('id', $id)->update(['sort_order' => $index]);
        }
        return $this->success(null, 'Testimonials reordered');
    }

    /**
     * Import testimonials from external source.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'source' => 'required|string|in:google,facebook,trustpilot,csv',
            'data' => 'required|array',
        ]);
        
        $imported = 0;
        foreach ($request->data as $item) {
            Testimonial::create([
                'author_name' => $item['author_name'] ?? 'Anonymous',
                'author_company' => $item['company'] ?? null,
                'rating' => $item['rating'] ?? 5,
                'is_active' => false,
                'external_source' => $request->source,
                'external_id' => $item['external_id'] ?? null,
            ]);
            $imported++;
        }
        
        return $this->success(['imported' => $imported], 'Testimonials imported');
    }

    /**
     * Update overall rating statistics.
     */
    public function updateRatingStats(): JsonResponse
    {
        $stats = [
            'total' => Testimonial::active()->count(),
            'average_rating' => Testimonial::active()->avg('rating'),
            'rating_distribution' => Testimonial::active()
                ->selectRaw('rating, count(*) as count')
                ->groupBy('rating')
                ->pluck('count', 'rating'),
        ];
        
        return $this->success($stats);
    }

    /**
     * Get featured testimonials.
     */
    public function featured(Request $request): JsonResponse
    {
        $testimonials = Testimonial::with(['translation', 'avatar'])
            ->active()
            ->featured()
            ->orderBy('sort_order')
            ->limit($request->integer('limit', 6))
            ->get();
        
        return $this->success(TestimonialResource::collection($testimonials));
    }
}
