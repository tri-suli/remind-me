<?php

namespace App\Http\Controllers\Service\User;

use App\Exceptions\CredentialMismatchException;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * The auth service instance
     *
     * @var AuthService
     */
    public readonly AuthService $authService;

    /**
     * Create a new controller instance
     *
     * @param  AuthService  $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle the incoming request.
     *
     * @throws CredentialMismatchException
     */
    public function __invoke(Request $request): UserResource
    {
        $user = $this->authService->resolveLoginUser();

        if (is_null($user)) {
            throw new CredentialMismatchException(__('passwords.user'));
        }

        $this->authService->giveTokensToUser($user);

        return new UserResource($user);
    }
}
