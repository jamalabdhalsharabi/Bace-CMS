<?php

declare(strict_types=1);

namespace Modules\Users\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Modules\Users\Contracts\UserRepositoryContract;
use Modules\Users\Contracts\UserServiceContract;
use Modules\Users\Domain\Models\User;
use Modules\Users\Http\DTOs\CreateUserDTO;
use Modules\Users\Http\DTOs\UpdateUserDTO;

class UserService implements UserServiceContract
{
    public function __construct(
        protected UserRepositoryContract $repository
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with('profile');

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['verified'])) {
            $query->verified();
        }

        return $query->latest()->paginate($perPage);
    }

    public function find(string $id): ?User
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    public function create(CreateUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->repository->create($dto->toUserArray());

            $profileData = $dto->toProfileArray();
            if (!empty($profileData)) {
                $user->profile()->create($profileData);
            } else {
                $user->profile()->create([]);
            }

            return $user->fresh(['profile']);
        });
    }

    public function update(User $user, UpdateUserDTO $dto): User
    {
        return DB::transaction(function () use ($user, $dto) {
            $userData = $dto->toUserArray();
            if (!empty($userData)) {
                $this->repository->update($user, $userData);
            }

            $profileData = $dto->toProfileArray();
            if (!empty($profileData)) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $profileData
                );
            }

            return $user->fresh(['profile']);
        });
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return $user->fresh(['profile']);
    }

    public function updateAvatar(User $user, UploadedFile $file): User
    {
        $disk = config('users.avatars.disk', 'public');
        $path = config('users.avatars.path', 'avatars');

        // Delete old avatar
        if ($user->profile?->avatar) {
            Storage::disk($disk)->delete($user->profile->avatar);
        }

        // Store new avatar
        $avatarPath = $file->store($path, $disk);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['avatar' => $avatarPath]
        );

        return $user->fresh(['profile']);
    }

    public function removeAvatar(User $user): User
    {
        if ($user->profile?->avatar) {
            $disk = config('users.avatars.disk', 'public');
            Storage::disk($disk)->delete($user->profile->avatar);

            $user->profile->update(['avatar' => null]);
        }

        return $user->fresh(['profile']);
    }

    public function changePassword(User $user, string $password): User
    {
        $user->update(['password' => Hash::make($password)]);

        return $user;
    }

    public function activate(User $user): User
    {
        return $user->activate();
    }

    public function suspend(User $user): User
    {
        return $user->suspend();
    }

    public function delete(User $user): bool
    {
        // Delete avatar
        if ($user->profile?->avatar) {
            $disk = config('users.avatars.disk', 'public');
            Storage::disk($disk)->delete($user->profile->avatar);
        }

        return $this->repository->delete($user);
    }
}
