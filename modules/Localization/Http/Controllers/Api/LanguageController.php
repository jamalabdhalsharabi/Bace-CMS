<?php

declare(strict_types=1);

namespace Modules\Localization\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Localization\Contracts\LanguageServiceContract;
use Modules\Localization\Http\Requests\CreateLanguageRequest;
use Modules\Localization\Http\Requests\UpdateLanguageRequest;
use Modules\Localization\Http\Resources\LanguageResource;

class LanguageController extends BaseController
{
    public function __construct(
        protected LanguageServiceContract $languageService
    ) {}

    public function index(): JsonResponse
    {
        $languages = $this->languageService->getActive();

        return $this->success(LanguageResource::collection($languages));
    }

    public function all(): JsonResponse
    {
        $languages = $this->languageService->all();

        return $this->success(LanguageResource::collection($languages));
    }

    public function show(string $id): JsonResponse
    {
        $language = $this->languageService->find($id);

        if (!$language) {
            return $this->notFound('Language not found');
        }

        return $this->success(new LanguageResource($language));
    }

    public function store(CreateLanguageRequest $request): JsonResponse
    {
        $language = $this->languageService->create($request->validated());

        return $this->created(new LanguageResource($language));
    }

    public function update(UpdateLanguageRequest $request, string $id): JsonResponse
    {
        $language = $this->languageService->find($id);

        if (!$language) {
            return $this->notFound('Language not found');
        }

        $language = $this->languageService->update($language, $request->validated());

        return $this->success(new LanguageResource($language));
    }

    public function destroy(string $id): JsonResponse
    {
        $language = $this->languageService->find($id);

        if (!$language) {
            return $this->notFound('Language not found');
        }

        try {
            $this->languageService->delete($language);

            return $this->success(null, 'Language deleted');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
