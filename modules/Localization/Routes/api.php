<?php

use Illuminate\Support\Facades\Route;
use Modules\Localization\Http\Controllers\Api\LanguageController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    Route::get('/languages', [LanguageController::class, 'index'])->name('api.v1.languages.index');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::get('/languages/all', [LanguageController::class, 'all'])->name('api.v1.languages.all');
        Route::post('/languages', [LanguageController::class, 'store'])->name('api.v1.languages.store');
        Route::get('/languages/{id}', [LanguageController::class, 'show'])->name('api.v1.languages.show');
        Route::put('/languages/{id}', [LanguageController::class, 'update'])->name('api.v1.languages.update');
        Route::delete('/languages/{id}', [LanguageController::class, 'destroy'])->name('api.v1.languages.destroy');

        // Status
        Route::post('/languages/{id}/activate', [LanguageController::class, 'activate'])->name('api.v1.languages.activate');
        Route::post('/languages/{id}/deactivate', [LanguageController::class, 'deactivate'])->name('api.v1.languages.deactivate');
        Route::post('/languages/{id}/default', [LanguageController::class, 'setDefault'])->name('api.v1.languages.set-default');
        Route::post('/languages/reorder', [LanguageController::class, 'reorder'])->name('api.v1.languages.reorder');
        Route::put('/languages/{id}/fallback', [LanguageController::class, 'setFallback'])->name('api.v1.languages.fallback');

        // Translations Files
        Route::get('/translations/{locale}/{group}', [LanguageController::class, 'getTranslationFile'])->name('api.v1.translations.get');
        Route::put('/translations/{locale}/{group}', [LanguageController::class, 'updateTranslationFile'])->name('api.v1.translations.update');
        Route::post('/translations/{locale}/import', [LanguageController::class, 'importTranslations'])->name('api.v1.translations.import');
        Route::get('/translations/{locale}/export', [LanguageController::class, 'exportTranslations'])->name('api.v1.translations.export');
        Route::post('/translations/sync', [LanguageController::class, 'syncTranslations'])->name('api.v1.translations.sync');

        // Auto-translate
        Route::post('/translations/auto-translate', [LanguageController::class, 'autoTranslate'])->name('api.v1.translations.auto-translate');

        // Progress
        Route::get('/translations/{locale}/progress', [LanguageController::class, 'getProgress'])->name('api.v1.translations.progress');
        Route::get('/translations/{locale}/missing', [LanguageController::class, 'getMissing'])->name('api.v1.translations.missing');

        // Missing: Translation Keys, Translator Assignment, Cleanup
        Route::post('/translations/keys', [LanguageController::class, 'addTranslationKey'])->name('api.v1.translations.add-key');
        Route::delete('/translations/keys/{key}', [LanguageController::class, 'deleteTranslationKey'])->name('api.v1.translations.delete-key');
        Route::post('/translations/{locale}/review', [LanguageController::class, 'reviewTranslation'])->name('api.v1.translations.review');
        Route::post('/translations/{locale}/approve', [LanguageController::class, 'approveTranslation'])->name('api.v1.translations.approve');
        Route::post('/translations/{locale}/reject', [LanguageController::class, 'rejectTranslation'])->name('api.v1.translations.reject');
        Route::post('/languages/{id}/translator', [LanguageController::class, 'assignTranslator'])->name('api.v1.languages.assign-translator');
        Route::delete('/languages/{id}/translator', [LanguageController::class, 'unassignTranslator'])->name('api.v1.languages.unassign-translator');
        Route::post('/translations/cleanup', [LanguageController::class, 'cleanupUnused'])->name('api.v1.translations.cleanup');
        Route::post('/translations/optimize', [LanguageController::class, 'optimizeCache'])->name('api.v1.translations.optimize');
    });
});
