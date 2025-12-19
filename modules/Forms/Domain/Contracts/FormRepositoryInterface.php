<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Contracts\RepositoryInterface;
use Modules\Forms\Domain\Models\Form;

/**
 * Form Repository Interface.
 *
 * @extends RepositoryInterface<Form>
 *
 * @package Modules\Forms\Domain\Contracts
 */
interface FormRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated forms with filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find form by slug.
     */
    public function findBySlug(string $slug): ?Form;

    /**
     * Get all active forms.
     */
    public function getActive(): Collection;
}
