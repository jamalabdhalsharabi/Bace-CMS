<?php

declare(strict_types=1);

namespace Modules\Localization\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Localization\Contracts\LanguageServiceContract;
use Modules\Localization\Http\Requests\CreateLanguageRequest;
use Modules\Localization\Http\Requests\UpdateLanguageRequest;
use Modules\Localization\Http\Resources\LanguageResource;

/**
 * Class LanguageController
 * 
 * API controller for managing languages and translations.
 * 
 * @package Modules\Localization\Http\Controllers\Api
 */
class LanguageController extends BaseController
{
    /**
     * The language service instance for handling language-related business logic.
     *
     * @var LanguageServiceContract
     */
    protected LanguageServiceContract $languageService;

    /**
     * Create a new LanguageController instance.
     *
     * @param LanguageServiceContract $languageService The language service contract implementation
     */
    public function __construct(
        LanguageServiceContract $languageService
    ) {
        $this->languageService = $languageService;
    }

    /**
     * Display a listing of active languages.
     *
     * @return JsonResponse Collection of active languages
     */
    public function index(): JsonResponse
    {
        $languages = $this->languageService->getActive();

        return $this->success(LanguageResource::collection($languages));
    }

    /**
     * Display all languages including inactive ones.
     *
     * @return JsonResponse Collection of all languages
     */
    public function all(): JsonResponse
    {
        $languages = $this->languageService->all();

        return $this->success(LanguageResource::collection($languages));
    }

    /**
     * Display the specified language by its UUID.
     *
     * @param string $id The UUID of the language to retrieve
     * @return JsonResponse The language data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $language = $this->languageService->find($id);

        if (!$language) {
            return $this->notFound('Language not found');
        }

        return $this->success(new LanguageResource($language));
    }

    /**
     * Store a newly created language in the database.
     *
     * @param CreateLanguageRequest $request The validated request containing language data
     * @return JsonResponse The newly created language (HTTP 201)
     */
    public function store(CreateLanguageRequest $request): JsonResponse
    {
        $language = $this->languageService->create($request->validated());

        return $this->created(new LanguageResource($language));
    }

    /**
     * Update the specified language in the database.
     *
     * @param UpdateLanguageRequest $request The validated request containing updated data
     * @param string $id The UUID of the language to update
     * @return JsonResponse The updated language or 404 error
     */
    public function update(UpdateLanguageRequest $request, string $id): JsonResponse
    {
        $language = $this->languageService->find($id);

        if (!$language) {
            return $this->notFound('Language not found');
        }

        $language = $this->languageService->update($language, $request->validated());

        return $this->success(new LanguageResource($language));
    }

    /**
     * Delete the specified language.
     *
     * @param string $id The UUID of the language to delete
     * @return JsonResponse Success message or error
     * @throws \RuntimeException If the language cannot be deleted
     */
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
