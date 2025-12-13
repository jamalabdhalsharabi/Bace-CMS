<?php

declare(strict_types=1);

namespace Modules\Localization\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Localization\Application\Services\LanguageCommandService;
use Modules\Localization\Application\Services\LanguageQueryService;
use Modules\Localization\Http\Requests\CreateLanguageRequest;
use Modules\Localization\Http\Requests\UpdateLanguageRequest;
use Modules\Localization\Http\Requests\SetFallbackRequest;
use Modules\Localization\Http\Requests\ImportPackRequest;
use Modules\Localization\Http\Requests\AddTranslationKeyRequest;
use Modules\Localization\Http\Requests\UpdateTranslationRequest;
use Modules\Localization\Http\Requests\AutoTranslateRequest;
use Modules\Localization\Http\Resources\LanguageResource;

class LanguageController extends BaseController
{
    public function __construct(
        protected LanguageQueryService $queryService,
        protected LanguageCommandService $commandService
    ) {
    }

    public function index(): JsonResponse
    {
        $languages = $this->queryService->getActive();
        return $this->success(LanguageResource::collection($languages));
    }

    public function all(): JsonResponse
    {
        $languages = $this->queryService->getAll();
        return $this->success(LanguageResource::collection($languages));
    }

    public function show(string $id): JsonResponse
    {
        $language = $this->queryService->findById($id);
        if (!$language) {
            return $this->notFound('Language not found');
        }
        return $this->success(new LanguageResource($language));
    }

    public function store(CreateLanguageRequest $request): JsonResponse
    {
        $language = $this->commandService->create($request->validated());
        return $this->created(new LanguageResource($language));
    }

    public function update(UpdateLanguageRequest $request, string $id): JsonResponse
    {
        $language = $this->queryService->findById($id);
        if (!$language) {
            return $this->notFound('Language not found');
        }
        $language = $this->commandService->update($id, $request->validated());
        return $this->success(new LanguageResource($language));
    }

    public function destroy(string $id): JsonResponse
    {
        $language = $this->queryService->findById($id);
        if (!$language) return $this->notFound('Language not found');
        try {
            $this->commandService->delete($id);
            return $this->success(null, 'Language deleted');
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /** Activate a language. */
    public function activate(string $id): JsonResponse
    {
        $language = $this->queryService->findById($id);
        if (!$language) return $this->notFound('Language not found');
        $language = $this->commandService->activate($id);
        return $this->success(new LanguageResource($language), 'Language activated');
    }

    /** Deactivate a language. */
    public function deactivate(string $id): JsonResponse
    {
        $language = $this->queryService->findById($id);
        if (!$language) return $this->notFound('Language not found');
        $language = $this->commandService->deactivate($id);
        return $this->success(new LanguageResource($language), 'Language deactivated');
    }

    /** Set default language. */
    public function setDefault(string $id): JsonResponse
    {
        $language = $this->queryService->findById($id);
        if (!$language) return $this->notFound('Language not found');
        $language = $this->commandService->setDefault($id);
        return $this->success(new LanguageResource($language), 'Default language set');
    }

    /** Set fallback chain. */
    public function setFallback(SetFallbackRequest $request, string $id): JsonResponse
    {
        $language = $this->queryService->findById($id);
        if (!$language) return $this->notFound('Language not found');
        $language = $this->commandService->setFallback($id, $request->fallback_locale);
        return $this->success(new LanguageResource($language));
    }

    /** Sync translation files. */
    public function syncTranslationFiles(): JsonResponse
    {
        $result = $this->commandService->syncTranslationFiles();
        return $this->success($result, 'Translation files synced');
    }

    /** Import language pack. */
    public function importPack(ImportPackRequest $request): JsonResponse
    {
        $result = $this->commandService->importPack($request->file('file'), $request->locale);
        return $this->success($result, 'Language pack imported');
    }

    /** Export translation files. */
    public function exportTranslations(\Illuminate\Http\Request $request): JsonResponse
    {
        $result = $this->commandService->exportTranslations(
            $request->input('locale'),
            $request->input('format', 'json')
        );
        return $this->success($result);
    }

    /** Add translation key. */
    public function addTranslationKey(AddTranslationKeyRequest $request): JsonResponse
    {
        $result = $this->commandService->addTranslationKey($request->key, $request->group, $request->translations);
        return $this->created($result, 'Translation key added');
    }

    /** Update translation. */
    public function updateTranslation(UpdateTranslationRequest $request): JsonResponse
    {
        $result = $this->commandService->updateTranslation($request->key, $request->locale, $request->value);
        return $this->success($result, 'Translation updated');
    }

    /** Delete translation key. */
    public function deleteTranslationKey(\Illuminate\Http\Request $request): JsonResponse
    {
        $request->validate(['key' => 'required|string']);
        $this->commandService->deleteTranslationKey($request->key);
        return $this->success(null, 'Translation key deleted');
    }

    /** Review translation. */
    public function reviewTranslation(\Illuminate\Http\Request $request, string $translationId): JsonResponse
    {
        $result = $this->commandService->reviewTranslation($translationId, $request->notes);
        return $this->success($result, 'Translation reviewed');
    }

    /** Approve translation. */
    public function approveTranslation(string $translationId): JsonResponse
    {
        $result = $this->commandService->approveTranslation($translationId);
        return $this->success($result, 'Translation approved');
    }

    /** Reject translation. */
    public function rejectTranslation(\Illuminate\Http\Request $request, string $translationId): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);
        $result = $this->commandService->rejectTranslation($translationId, $request->reason);
        return $this->success($result, 'Translation rejected');
    }

    /** Publish translation. */
    public function publishTranslation(string $translationId): JsonResponse
    {
        $result = $this->commandService->publishTranslation($translationId);
        return $this->success($result, 'Translation published');
    }

    /** Check missing translations. */
    public function checkMissing(\Illuminate\Http\Request $request): JsonResponse
    {
        $missing = $this->queryService->checkMissingTranslations($request->input('locale'));
        return $this->success(['missing' => $missing, 'count' => count($missing)]);
    }

    /** Auto-translate using AI/API. */
    public function autoTranslate(AutoTranslateRequest $request): JsonResponse
    {
        $result = $this->commandService->autoTranslate(
            $request->source_locale,
            $request->target_locale,
            $request->keys
        );
        return $this->success($result, 'Auto-translation completed');
    }

    /** Assign translator. */
    public function assignTranslator(\Illuminate\Http\Request $request, string $id): JsonResponse
    {
        $request->validate(['user_id' => 'required|uuid']);
        $result = $this->commandService->assignTranslator($id, $request->user_id);
        return $this->success($result, 'Translator assigned');
    }

    /** Unassign translator. */
    public function unassignTranslator(string $id): JsonResponse
    {
        $this->commandService->unassignTranslator($id);
        return $this->success(null, 'Translator unassigned');
    }

    /** Get translation progress. */
    public function translationProgress(\Illuminate\Http\Request $request): JsonResponse
    {
        $progress = $this->queryService->getTranslationProgress($request->input('locale'));
        return $this->success($progress);
    }

    /** Optimize translation performance. */
    public function optimizePerformance(): JsonResponse
    {
        $result = $this->commandService->optimizePerformance();
        return $this->success($result, 'Performance optimized');
    }

    /** Clean unused translations. */
    public function cleanUnused(): JsonResponse
    {
        $count = $this->commandService->cleanUnusedTranslations();
        return $this->success(['deleted' => $count], 'Unused translations cleaned');
    }
}
