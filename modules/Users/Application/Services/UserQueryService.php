<?php

declare(strict_types=1);

namespace Modules\Users\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Users\Domain\Models\User;
use Modules\Users\Domain\Repositories\UserRepository;

/**
 * User Query Service.
 */
final class UserQueryService
{
    public function __construct(
        private readonly UserRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['profile', 'roles'])
            ->getPaginated($filters, $perPage);
    }

    public function find(string $id): ?User
    {
        return $this->repository
            ->with(['profile', 'roles', 'permissions'])
            ->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository
            ->with(['profile'])
            ->findByEmail($email);
    }
}
