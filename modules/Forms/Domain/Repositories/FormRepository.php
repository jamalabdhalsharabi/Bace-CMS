<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Forms\Domain\Contracts\FormRepositoryInterface;
use Modules\Forms\Domain\Models\Form;

/**
 * Form Repository Implementation.
 *
 * Read-only repository for Form model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * @extends BaseRepository<Form>
 * @implements FormRepositoryInterface
 *
 * @package Modules\Forms\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class FormRepository extends BaseRepository implements FormRepositoryInterface
{
    /**
     * Create a new FormRepository instance.
     *
     * @param Form $model The Form model instance
     */
    public function __construct(Form $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated forms with optional filters.
     *
     * Uses withCount for submissions to minimize queries.
     *
     * @param array<string, mixed> $filters Available filters: type, active, search
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Form>
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()->withCount('submissions');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'LIKE', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find an active form by its slug.
     *
     * @param string $slug The form slug
     *
     * @return Form|null
     */
    public function findBySlug(string $slug): ?Form
    {
        return $this->query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with('fields')
            ->first();
    }

    /**
     * Get all active forms.
     *
     * @return Collection<int, Form>
     */
    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get form with fields and submissions count.
     *
     * @param string $id The form ID
     *
     * @return Form|null
     */
    public function findWithDetails(string $id): ?Form
    {
        return $this->query()
            ->with('fields')
            ->withCount('submissions')
            ->find($id);
    }

    /**
     * Get form statistics using optimized single query.
     *
     * @param string $id The form ID
     *
     * @return array{submissions_count: int, today_count: int, week_count: int}
     */
    public function getStats(string $id): array
    {
        $form = $this->find($id);
        
        if (!$form) {
            return ['submissions_count' => 0, 'today_count' => 0, 'week_count' => 0];
        }

        // Single query with conditional aggregates for optimization
        $stats = $form->submissions()->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
            SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as week
        ", [now()->startOfWeek()])->first();

        return [
            'submissions_count' => (int) ($stats->total ?? 0),
            'today_count' => (int) ($stats->today ?? 0),
            'week_count' => (int) ($stats->week ?? 0),
        ];
    }

    /**
     * Get paginated submissions for a form.
     *
     * @param string $formId The form ID
     * @param array<string, mixed> $filters Available filters: status, exclude_spam
     * @param int $perPage Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSubmissions(string $formId, array $filters = [], int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = \Modules\Forms\Domain\Models\FormSubmission::where('form_id', $formId)
            ->with('user');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['exclude_spam']) && $filters['exclude_spam']) {
            $query->where('status', '!=', 'spam');
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find a submission by ID.
     *
     * @param string $submissionId The submission UUID
     *
     * @return \Modules\Forms\Domain\Models\FormSubmission|null
     */
    public function findSubmission(string $submissionId): ?\Modules\Forms\Domain\Models\FormSubmission
    {
        return \Modules\Forms\Domain\Models\FormSubmission::with(['form', 'user', 'fieldValues'])
            ->find($submissionId);
    }
}
