<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\EloquentRepository;

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

        return $this->createProfile($user, [
            'first_name' => $attributes['firstName'],
            'last_name'  => $attributes['lastName'],
            'gender'     => $attributes['gender'] ?? null,
            'dob'        => $attributes['dob'],
            'timezone'   => $attributes['timezone'],
        ]);
    }
}
