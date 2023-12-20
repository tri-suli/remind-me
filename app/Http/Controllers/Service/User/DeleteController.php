<?php

namespace App\Http\Controllers\Service\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Repositories\EloquentRepository;
use Illuminate\Http\Request;

class DeleteController extends Controller
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
        $this->needsAccessApiToken();
        $this->eloquentRepository = $eloquentRepository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): MessageResource
    {
        $user = $request->user();

        $this->eloquentRepository->delete($user->id);

        return new MessageResource('Goodbye!');
    }
}
