<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\EloquentRepository;
use Illuminate\Support\Facades\Date;

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
            'timezone'   => $attributes['timezone'],
        ]);

        $accessToken = $this->createAccessToken($user);
        $refreshToken = $this->createRefreshToken($user);

        $user->access_token = $accessToken;
        $user->refresh_token = $refreshToken;

        return $user;
    }
}
