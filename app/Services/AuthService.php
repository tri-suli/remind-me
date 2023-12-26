<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class AuthService
 *
 * @TODO: Handle logout user
 */
class AuthService
{
    /**
     * Eloquent user repository instance
     *
     * @var EloquentUserRepository
     */
    public readonly EloquentUserRepository $userRepository;

    /**
     * The illuminate request instance
     *
     * @var Request
     */
    public readonly Request $request;

    /**
     * Create a new service instance
     *
     * @param  Request  $request
     * @param  EloquentUserRepository  $userRepository
     */
    public function __construct(Request $request, EloquentUserRepository $userRepository)
    {
        $this->request = $request;
        $this->userRepository = $userRepository;
    }

    /**
     * Handle login user and return the authenticated user
     *
     * @return User|null
     */
    public function resolveLoginUser(): ?User
    {
        if ($this->isFromThirdPartyRequest()) {
            $user = $this->currentUser();

            if ($this->validateUserCredential($user->password)) {
                return $user;
            }
        } else {
            if ($this->validateUserCredential()) {
                $this->request->session()->regenerate();

                return $this->currentUser();
            }
        }

        return null;
    }

    /**
     * Get current authenticated user.
     *
     * @return User|null
     */
    public function currentUser(): ?User
    {
        // If there's no current authenticated user, we will get the user
        // from database base on the request credential values
        $user = $this->request->user();

        if (! ($user instanceof User)) {
            return $this->userRepository->firstByEmail($this->credential()['email']);
        }

        return $user;
    }

    /**
     * Get current credential values from request object
     *
     * @return array
     */
    public function credential(): array
    {
        $credential = $this->request->only('email', 'password');

        if (empty($credential)) {
            return ['email' => '', 'password' => ''];
        }

        return $credential;
    }

    /**
     * Generate access token & refresh token for the given user
     *
     * @param  User  $user
     * @return void
     */
    public function giveTokensToUser(User $user): void
    {
        $user->access_token = $this->userRepository->createAccessToken($user);
        $user->refresh_token = $this->userRepository->createRefreshToken($user);
    }

    /**
     * Check if the request is coming from third party apps
     *
     * @return bool
     */
    public function isFromThirdPartyRequest(): bool
    {
        return $this->request->hasHeader('device-name');
    }

    /**
     * Validate user credential request
     *
     * @param  string  $passwordHashed
     * @return bool
     */
    protected function validateUserCredential(string $passwordHashed = ''): bool
    {
        // If the given hashed password is empty meaning, user is login
        // from web app. If it has a value then the user is trying to log in
        // via third party apps

        if (empty($passwordHashed)) {
            return Auth::attempt($this->credential());
        }

        $password = $this->credential()['password'];

        return Hash::check($password, $passwordHashed);
    }
}
