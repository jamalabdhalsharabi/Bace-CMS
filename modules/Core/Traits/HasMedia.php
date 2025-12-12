<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;

trait HasMedia
{
    /**
     * Get all media for this model.
     */
    public function media(): MorphMany
    {
        return $this->morphMany($this->getMediaModelClass(), 'mediable')
            ->orderBy('ordering');
    }

    /**
     * Get featured/primary media.
     */
    public function featuredMedia(): MorphOne
    {
        return $this->morphOne($this->getMediaModelClass(), 'mediable')
            ->where('is_featured', true);
    }

    /**
     * Get media by collection.
     */
    public function getMedia(string $collection = 'default'): mixed
    {
        return $this->media()->where('collection', $collection)->get();
    }

    /**
     * Get first media from collection.
     */
    public function getFirstMedia(string $collection = 'default'): mixed
    {
        return $this->media()->where('collection', $collection)->first();
    }

    /**
     * Get first media URL.
     */
    public function getFirstMediaUrl(string $collection = 'default', string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia($collection);

        if (!$media) {
            return null;
        }

        if ($conversion && method_exists($media, 'getUrl')) {
            return $media->getUrl($conversion);
        }

        return $media->url ?? null;
    }

    /**
     * Check if model has media.
     */
    public function hasMedia(string $collection = 'default'): bool
    {
        return $this->media()->where('collection', $collection)->exists();
    }

    /**
     * Attach media to model.
     */
    public function attachMedia(mixed $media, string $collection = 'default', array $attributes = []): static
    {
        if (is_array($media)) {
            foreach ($media as $item) {
                $this->attachSingleMedia($item, $collection, $attributes);
            }
        } else {
            $this->attachSingleMedia($media, $collection, $attributes);
        }

        return $this;
    }

    /**
     * Attach single media item.
     */
    protected function attachSingleMedia(mixed $media, string $collection, array $attributes): void
    {
        $mediaClass = $this->getMediaModelClass();

        if ($media instanceof UploadedFile) {
            // Handle file upload through Media module
            // This would typically call a MediaService
            return;
        }

        if (is_string($media) || is_int($media)) {
            // Attach existing media by ID
            $mediaModel = $mediaClass::find($media);
            if ($mediaModel) {
                $mediaModel->update(array_merge([
                    'mediable_type' => get_class($this),
                    'mediable_id' => $this->id,
                    'collection' => $collection,
                ], $attributes));
            }
            return;
        }

        if ($media instanceof $mediaClass) {
            $media->update(array_merge([
                'mediable_type' => get_class($this),
                'mediable_id' => $this->id,
                'collection' => $collection,
            ], $attributes));
        }
    }

    /**
     * Detach media from model.
     */
    public function detachMedia(mixed $media = null, ?string $collection = null): static
    {
        $query = $this->media();

        if ($collection) {
            $query->where('collection', $collection);
        }

        if ($media) {
            $ids = is_array($media) ? $media : [$media];
            $query->whereIn('id', $ids);
        }

        $query->update([
            'mediable_type' => null,
            'mediable_id' => null,
        ]);

        return $this;
    }

    /**
     * Clear all media from collection.
     */
    public function clearMedia(?string $collection = null): static
    {
        return $this->detachMedia(null, $collection);
    }

    /**
     * Sync media for collection.
     */
    public function syncMedia(array $mediaIds, string $collection = 'default'): static
    {
        $this->clearMedia($collection);

        foreach ($mediaIds as $index => $mediaId) {
            $this->attachMedia($mediaId, $collection, ['ordering' => $index]);
        }

        return $this;
    }

    /**
     * Set featured media.
     */
    public function setFeaturedMedia(mixed $media): static
    {
        // Unset current featured
        $this->media()->update(['is_featured' => false]);

        // Set new featured
        $this->attachMedia($media, 'featured', ['is_featured' => true]);

        return $this;
    }

    /**
     * Get media model class.
     */
    protected function getMediaModelClass(): string
    {
        return $this->mediaModel ?? 'Modules\\Media\\Domain\\Models\\Media';
    }

    /**
     * Scope: With media.
     */
    public function scopeWithMedia($query, ?string $collection = null)
    {
        return $query->with(['media' => function ($q) use ($collection) {
            if ($collection) {
                $q->where('collection', $collection);
            }
            $q->orderBy('ordering');
        }]);
    }
}
