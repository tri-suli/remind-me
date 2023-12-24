<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;

/**
 * @extends EloquentRepository
 *
 * @method User create(array $attributes)
 * @method User|null find(int $id)
 * @method User update(int $id, array $attributes)
 */
class EloquentUserRepository extends EloquentRepository
{
    /**
     * {@inheritDoc}
     */
    protected function eloquent(): string
    {
        return User::class;
    }

    /**
     * Create user access token
     *
     * @param  User  $user
     * @return string
     */
    public function createAccessToken(User $user): string
    {
        $expireAfterMinutes = config('sanctum.expiration');
        $expiration = Date::now()->addMinutes($expireAfterMinutes);

        $accessToken = $user->createToken('access_token', ['access-api'], $expiration);

        return $accessToken->plainTextToken;
    }

    /**
     * Create user refresh token
     *
     * @param  User  $user
     * @return string
     */
    public function createRefreshToken(User $user): string
    {
        $expireAfterMinutes = config('sanctum.refresh_expiration');
        $expiration = Date::now()->addMinutes($expireAfterMinutes);

        $accessToken = $user->createToken('refresh_token', ['issue-access-token'], $expiration);

        return $accessToken->plainTextToken;
    }

    /**
     * Create a user profile record and return the user model instance
     *
     * @param  User  $user
     * @param  array  $attributes
     * @return User
     */
    public function createProfile(User $user, array $attributes): User
    {
        $user->profile()->create($attributes);

        return $this->model->where('id', $user->id)->with('profile')->first();
    }

    /**
     * Get the users who have birthday by today
     *
     * @return Collection
     */
    public function findBirthdayNow(): Collection
    {
        return $this->model->newQuery()
            ->select(['id', 'name', 'email'])
            ->whereHas('profile', function ($query) {
                $query->birthdayTimeNow();
            })
            ->with('profile:id,user_id,first_name,last_name,dob,gender,timezone')
            ->get();
    }

    /**
     * Get a user by email
     *
     * @param  string  $email
     * @return User|null
     */
    public function firstByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Create a new user record with their user's profile
     *
     * @param  array  $attributes
     * @return User
     */
    public function register(array $attributes): User
    {
        $user = $this->create([
            'name'     => $attributes['email'],
            'email'    => $attributes['email'],
            'password' => bcrypt($attributes['password']),
        ]);

        $user = $this->createProfile($user, [
            'first_name' => $attributes['firstName'],
            'last_name'  => $attributes['lastName'],
            'gender'     => $attributes['gender'] ?? null,
            'dob'        => $attributes['dob'],
            'timezone'   => $attributes['location'],
        ]);

        $accessToken = $this->createAccessToken($user);
        $refreshToken = $this->createRefreshToken($user);

        $user->access_token = $accessToken;
        $user->refresh_token = $refreshToken;

        return $user;
    }

    /**
     * Update existing user record including user profile by specified user's id
     *
     * @param  int  $id
     * @param  array  $attributes
     * @return User
     */
    public function updateWithProfile(int $id, array $attributes): User
    {
        $user = $this->find($id);
        $user = $this->update($id, [
            'name'  => $attributes['userName'] ?? $user->name,
            'email' => $attributes['email'] ?? $user->email,
        ]);

        $userProfile = $user->profile;
        $userProfile->update([
            'first_name' => $attributes['firstName'] ?? $userProfile->first_name,
            'last_name'  => $attributes['lastName'] ?? $userProfile->last_name,
            'gender'     => $attributes['gender'] ?? $userProfile->gender,
            'dob'        => $attributes['dob'] ?? $userProfile->dob,
            'timezone'   => $attributes['location'] ?? $userProfile->timezone,
        ]);

        return $user;
    }
}
