<?php

use Illuminate\Support\Facades\Route;
use Modules\Localization\Http\Controllers\Api\LanguageController;

Route::prefix('api/v1/languages')->middleware(['api'])->name('api.v1.languages.')->group(function () {
    // Public routes
    Route::get('/', [LanguageController::class, 'index'])->name('index');
    Route::get('/all', [LanguageController::class, 'all'])->name('all');
    Route::get('/{id}', [LanguageController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [LanguageController::class, 'store'])->name('store');
        Route::put('/{id}', [LanguageController::class, 'update'])->name('update');
        Route::delete('/{id}', [LanguageController::class, 'destroy'])->name('destroy');

        // Status
        Route::post('/{id}/activate', [LanguageController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [LanguageController::class, 'deactivate'])->name('deactivate');
        Route::post('/{id}/set-default', [LanguageController::class, 'setDefault'])->name('set-default');
        Route::post('/{id}/set-fallback', [LanguageController::class, 'setFallback'])->name('set-fallback');

        // Translations Management
        Route::post('/sync-files', [LanguageController::class, 'syncTranslationFiles'])->name('sync-files');
        Route::post('/import-pack', [LanguageController::class, 'importPack'])->name('import-pack');
        Route::get('/export', [LanguageController::class, 'exportTranslations'])->name('export');
        Route::post('/translation-key', [LanguageController::class, 'addTranslationKey'])->name('add-key');
        Route::put('/translation', [LanguageController::class, 'updateTranslation'])->name('update-translation');
        Route::delete('/translation-key', [LanguageController::class, 'deleteTranslationKey'])->name('delete-key');

        // Translation Workflow
        Route::post('/translations/{translationId}/review', [LanguageController::class, 'reviewTranslation'])->name('review-translation');
        Route::post('/translations/{translationId}/approve', [LanguageController::class, 'approveTranslation'])->name('approve-translation');
        Route::post('/translations/{translationId}/reject', [LanguageController::class, 'rejectTranslation'])->name('reject-translation');
        Route::post('/translations/{translationId}/publish', [LanguageController::class, 'publishTranslation'])->name('publish-translation');

        // Tools
        Route::get('/check-missing', [LanguageController::class, 'checkMissing'])->name('check-missing');
        Route::post('/auto-translate', [LanguageController::class, 'autoTranslate'])->name('auto-translate');
        Route::get('/progress', [LanguageController::class, 'translationProgress'])->name('progress');
        Route::post('/optimize', [LanguageController::class, 'optimizePerformance'])->name('optimize');
        Route::post('/clean-unused', [LanguageController::class, 'cleanUnused'])->name('clean-unused');

        // Translator Assignment
        Route::post('/{id}/assign-translator', [LanguageController::class, 'assignTranslator'])->name('assign-translator');
        Route::post('/{id}/unassign-translator', [LanguageController::class, 'unassignTranslator'])->name('unassign-translator');
    });
});
