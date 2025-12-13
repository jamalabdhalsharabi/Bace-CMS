<?php

declare(strict_types=1);

namespace Modules\Forms\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Forms\Domain\Models\Form;
use Modules\Forms\Domain\Models\FormSubmission;
use Modules\Forms\Domain\Repositories\FormRepository;

/**
 * Form Query Service.
 */
final class FormQueryService
{
    public function __construct(
        private readonly FormRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['fields'])
            ->getPaginated($filters, $perPage);
    }

    public function find(string $id): ?Form
    {
        return $this->repository
            ->with(['fields'])
            ->find($id);
    }

    public function findBySlug(string $slug): ?Form
    {
        return $this->repository
            ->with(['fields'])
            ->findBySlug($slug);
    }

    public function getActive(): Collection
    {
        return $this->repository
            ->with(['fields'])
            ->getActive();
    }

    public function getSubmissions(string $formId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = FormSubmission::where('form_id', $formId)->with('user');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['exclude_spam']) && $filters['exclude_spam']) {
            $query->where('status', '!=', 'spam');
        }

        return $query->latest()->paginate($perPage);
    }
}
