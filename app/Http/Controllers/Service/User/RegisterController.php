<?php

namespace App\Http\Controllers\Service\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Repositories\EloquentRepository;

class RegisterController extends Controller
{
    /**
     * Eloquent user repository instance
     *
     * @var EloquentRepository
     */
    public readonly EloquentRepository $eloquentRepository;

    /**
     * Create a new controller instance
     *
     * @param  EloquentRepository  $eloquentRepository
     */
    public function __construct(EloquentRepository $eloquentRepository)
    {
        $this->eloquentRepository = $eloquentRepository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreUserRequest $request): UserResource
    {
        $user = $this->eloquentRepository->register(
            $request->only([
                'firstName',
                'lastName',
                'email',
                'password',
                'gender',
                'dob',
                'location',
            ])
        );

        return new UserResource($user);
    }
}
