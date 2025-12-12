<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Testimonials\Domain\Models\Testimonial;
use Modules\Testimonials\Http\Resources\TestimonialResource;

class TestimonialController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Testimonial::with(['translation', 'avatar'])->active();
        if ($request->boolean('featured')) $query->featured();
        $testimonials = $query->orderBy('sort_order')->paginate($request->integer('per_page', 10));
        return $this->paginated(TestimonialResource::collection($testimonials)->resource);
    }

    public function show(string $id): JsonResponse
    {
        $testimonial = Testimonial::with(['translations', 'avatar'])->find($id);
        return $testimonial ? $this->success(new TestimonialResource($testimonial)) : $this->notFound();
    }

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

    public function update(Request $request, string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->update($request->all());
        return $this->success(new TestimonialResource($testimonial->fresh()));
    }

    public function destroy(string $id): JsonResponse
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) return $this->notFound();
        $testimonial->delete();
        return $this->success(null, 'Testimonial deleted');
    }
}
