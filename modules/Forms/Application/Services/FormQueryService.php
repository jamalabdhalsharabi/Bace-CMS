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
 *
 * Handles all read operations for forms via Repository pattern.
 * No write operations - delegates to FormCommandService for mutations.
 *
 * @package Modules\Forms\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class FormQueryService
{
    /**
     * Create a new FormQueryService instance.
     *
     * @param FormRepository $repository The form repository
     */
    public function __construct(
        private readonly FormRepository $repository
    ) {}

    /**
     * Get paginated list of forms.
     *
     * @param array<string, mixed> $filters Available filters: type, active, search
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Form>
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Find a form by ID.
     *
     * @param string $id The form UUID
     *
     * @return Form|null
     */
    public function find(string $id): ?Form
    {
        return $this->repository->findWithDetails($id);
    }

    /**
     * Find a form by slug.
     *
     * @param string $slug The form slug
     *
     * @return Form|null
     */
    public function findBySlug(string $slug): ?Form
    {
        return $this->repository->findBySlug($slug);
    }

    /**
     * Get all active forms.
     *
     * @return Collection<int, Form>
     */
    public function getActive(): Collection
    {
        return $this->repository->getActive();
    }

    /**
     * Get paginated submissions for a form.
     *
     * @param string $formId The form UUID
     * @param array<string, mixed> $filters Available filters: status, exclude_spam
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<FormSubmission>
     */
    public function getSubmissions(string $formId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getSubmissions($formId, $filters, $perPage);
    }

    /**
     * Find a submission by ID.
     *
     * @param string $submissionId The submission UUID
     *
     * @return FormSubmission|null
     */
    public function findSubmission(string $submissionId): ?FormSubmission
    {
        return $this->repository->findSubmission($submissionId);
    }

    /**
     * Get form statistics.
     *
     * @param string $formId The form UUID
     *
     * @return array{submissions_count: int, today_count: int, week_count: int}
     */
    public function getStats(string $formId): array
    {
        return $this->repository->getStats($formId);
    }
}
