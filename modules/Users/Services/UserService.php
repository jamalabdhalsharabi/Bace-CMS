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

/**
 * Class UserService
 *
 * Service class for managing users including CRUD operations,
 * profile management, avatars, and user status.
 *
 * @package Modules\Users\Services
 */
class UserService implements UserServiceContract
{
    /**
     * The user repository instance.
     *
     * @var UserRepositoryContract
     */
    protected UserRepositoryContract $repository;

    /**
     * Create a new UserService instance.
     *
     * @param UserRepositoryContract $repository The user repository
     */
    public function __construct(
        UserRepositoryContract $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * Retrieve a paginated list of users with optional filtering.
     *
     * Supports filtering by search term (email/name), status, and
     * email verification. Results are ordered by creation date descending.
     *
     * @param array $filters Optional filters: 'search', 'status', 'verified'
     * @param int $perPage Number of results per page (default: 15)
     *
     * @return LengthAwarePaginator Paginated collection of User models
     */
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

    /**
     * Find a user by their UUID.
     *
     * Retrieves a single user with their profile relationship loaded.
     * Returns null if no user is found with the given ID.
     *
     * @param string $id The UUID of the user to find
     *
     * @return User|null The found User or null if not found
     */
    public function find(string $id): ?User
    {
        return $this->repository->find($id);
    }

    /**
     * Find a user by their email address.
     *
     * Searches for a user with the exact email match.
     * Useful for authentication and duplicate checking.
     *
     * @param string $email The email address to search for
     *
     * @return User|null The found User or null if not found
     */
    public function findByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    /**
     * Create a new user with profile.
     *
     * Creates a user record and associated profile in a database
     * transaction. The password is automatically hashed.
     *
     * @param CreateUserDTO $dto Data transfer object containing user data
     *
     * @return User The newly created User with profile loaded
     *
     * @throws \Throwable If the transaction fails
     */
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

    /**
     * Update an existing user and their profile.
     *
     * Updates user and profile data in a database transaction.
     * Creates profile if it doesn't exist. Only updates provided fields.
     *
     * @param User $user The user to update
     * @param UpdateUserDTO $dto Data transfer object containing update data
     *
     * @return User The updated User with fresh profile data
     *
     * @throws \Throwable If the transaction fails
     */
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

    /**
     * Update only the user's profile data.
     *
     * Updates or creates the profile record with the provided data.
     * Does not modify the main user record.
     *
     * @param User $user The user whose profile to update
     * @param array $data Profile data to update (name, phone, bio, etc.)
     *
     * @return User The user with refreshed profile data
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        return $user->fresh(['profile']);
    }

    /**
     * Upload and update the user's avatar image.
     *
     * Stores the uploaded file to the configured disk and path,
     * deletes any existing avatar, and updates the profile record.
     *
     * @param User $user The user to update avatar for
     * @param UploadedFile $file The uploaded avatar image file
     *
     * @return User The user with updated profile and avatar path
     */
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

    /**
     * Remove the user's avatar image.
     *
     * Deletes the avatar file from storage and clears the
     * avatar path from the user's profile.
     *
     * @param User $user The user to remove avatar from
     *
     * @return User The user with cleared avatar
     */
    public function removeAvatar(User $user): User
    {
        if ($user->profile?->avatar) {
            $disk = config('users.avatars.disk', 'public');
            Storage::disk($disk)->delete($user->profile->avatar);

            $user->profile->update(['avatar' => null]);
        }

        return $user->fresh(['profile']);
    }

    /**
     * Change the user's password.
     *
     * Hashes the new password using Laravel's Hash facade
     * and updates the user record.
     *
     * @param User $user The user to change password for
     * @param string $password The new plain-text password
     *
     * @return User The updated user
     */
    public function changePassword(User $user, string $password): User
    {
        $user->update(['password' => Hash::make($password)]);

        return $user;
    }

    /**
     * Activate a user account.
     *
     * Sets the user's status to 'active', allowing them to
     * log in and access the system.
     *
     * @param User $user The user to activate
     *
     * @return User The activated user
     */
    public function activate(User $user): User
    {
        return $user->activate();
    }

    /**
     * Suspend a user account.
     *
     * Sets the user's status to 'suspended', preventing them
     * from logging in or accessing the system.
     *
     * @param User $user The user to suspend
     *
     * @return User The suspended user
     */
    public function suspend(User $user): User
    {
        return $user->suspend();
    }

    /**
     * Delete a user and their associated data.
     *
     * Removes the user's avatar from storage if present,
     * then soft-deletes the user record.
     *
     * @param User $user The user to delete
     *
     * @return bool True if deletion was successful
     */
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
