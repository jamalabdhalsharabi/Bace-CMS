<?php

declare(strict_types=1);

namespace Modules\Settings\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Settings\Contracts\SettingsServiceContract;
use Modules\Settings\Http\Requests\UpdateSettingsRequest;

/**
 * Class SettingsController
 * 
 * API controller for managing application settings
 * including groups, caching, and public settings.
 * 
 * @package Modules\Settings\Http\Controllers\Api
 */
class SettingsController extends BaseController
{
    /**
     * The settings service instance for handling settings-related business logic.
     *
     * @var SettingsServiceContract
     */
    protected SettingsServiceContract $settingsService;

    /**
     * Create a new SettingsController instance.
     *
     * @param SettingsServiceContract $settingsService The settings service contract implementation
     */
    public function __construct(
        SettingsServiceContract $settingsService
    ) {
        $this->settingsService = $settingsService;
    }

    /**
     * Get publicly accessible settings.
     *
     * @return JsonResponse Public settings data
     */
    public function public(): JsonResponse
    {
        return $this->success($this->settingsService->public());
    }

    /**
     * Get all settings (admin only).
     *
     * @return JsonResponse All settings data
     */
    public function index(): JsonResponse
    {
        return $this->success($this->settingsService->all());
    }

    /**
     * Get settings for a specific group.
     *
     * @param string $group The settings group name
     * @return JsonResponse Settings for the specified group
     */
    public function group(string $group): JsonResponse
    {
        return $this->success($this->settingsService->group($group));
    }

    /**
     * Get a specific setting by its key.
     *
     * @param string $key The setting key
     * @return JsonResponse The setting value or 404 error
     */
    public function show(string $key): JsonResponse
    {
        if (!$this->settingsService->has($key)) {
            return $this->notFound('Setting not found');
        }

        return $this->success([
            'key' => $key,
            'value' => $this->settingsService->get($key),
        ]);
    }

    /**
     * Update multiple settings at once.
     *
     * @param UpdateSettingsRequest $request The validated request containing settings array
     * @return JsonResponse All settings after update
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $this->settingsService->setMany($request->validated()['settings']);

        return $this->success(
            $this->settingsService->all(),
            'Settings updated successfully'
        );
    }

    /**
     * Delete a specific setting.
     *
     * @param string $key The setting key to delete
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $key): JsonResponse
    {
        if (!$this->settingsService->forget($key)) {
            return $this->notFound('Setting not found');
        }

        return $this->success(null, 'Setting deleted');
    }

    /**
     * Clear the settings cache.
     *
     * Forces settings to be reloaded from the database.
     *
     * @return JsonResponse Success message
     */
    public function clearCache(): JsonResponse
    {
        $this->settingsService->clearCache();

        return $this->success(null, 'Settings cache cleared');
    }
}
