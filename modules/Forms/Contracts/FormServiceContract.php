<?php

declare(strict_types=1);

namespace Modules\Forms\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Forms\Domain\Models\Form;
use Modules\Forms\Domain\Models\FormSubmission;

interface FormServiceContract
{
    public function all(): Collection;

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): ?Form;

    public function findBySlug(string $slug): ?Form;

    public function create(array $data): Form;

    public function update(Form $form, array $data): Form;

    public function delete(Form $form): bool;

    public function submit(Form $form, array $data, array $meta = []): FormSubmission;

    public function getSubmissions(string $formId, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function updateSubmissionStatus(FormSubmission $submission, string $status): FormSubmission;
}
