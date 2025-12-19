<?php

declare(strict_types=1);

namespace Modules\Testimonials\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Contracts\RepositoryInterface;
use Modules\Testimonials\Domain\Models\Testimonial;

/**
 * Testimonial Repository Interface.
 *
 * Defines the contract for testimonial-specific data access operations.
 * Extends the base RepositoryInterface with testimonial-specific methods
 * for managing customer testimonials and reviews.
 *
 * @extends RepositoryInterface<Testimonial>
 *
 * @package Modules\Testimonials\Domain\Contracts
 * @author  CMS Development Team
 * @since   1.0.0
 */
interface TestimonialRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated testimonials with optional filters.
     *
     * @param array<string, mixed> $filters Filter criteria (featured, status, etc.)
     * @param int                  $perPage Items per page
     *
     * @return LengthAwarePaginator Paginated testimonials
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active testimonials ordered by sort order.
     *
     * @param int $limit Maximum number to retrieve
     *
     * @return Collection<int, Testimonial>
     */
    public function getActive(int $limit = 10): Collection;

    /**
     * Get featured active testimonials.
     *
     * @param int $limit Maximum number to retrieve
     *
     * @return Collection<int, Testimonial>
     */
    public function getFeatured(int $limit = 6): Collection;

    /**
     * Get approved testimonials.
     *
     * @return Collection<int, Testimonial>
     */
    public function getApproved(): Collection;

    /**
     * Get trashed testimonials.
     *
     * @param int $perPage Items per page
     *
     * @return LengthAwarePaginator
     */
    public function getTrashed(int $perPage = 15): LengthAwarePaginator;

    /**
     * Restore a soft-deleted testimonial.
     *
     * @param string $id Testimonial UUID
     *
     * @return Testimonial|null
     */
    public function restore(string $id): ?Testimonial;

    /**
     * Force delete a testimonial permanently.
     *
     * @param string $id Testimonial UUID
     *
     * @return bool
     */
    public function forceDelete(string $id): bool;

    /**
     * Update sort order for multiple testimonials.
     *
     * @param array<int, string> $order Array of testimonial IDs in desired order
     *
     * @return void
     */
    public function reorder(array $order): void;

    /**
     * Get rating statistics.
     *
     * @return array{total: int, average: float, distribution: array}
     */
    public function getRatingStats(): array;
}
