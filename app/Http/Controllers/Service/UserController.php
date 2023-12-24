<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Repositories\EloquentRepository;
use Illuminate\Http\Request;

class UserController extends Controller
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
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): UserResource
    {
        $user = $this->eloquentRepository->updateWithProfile($request->id,
            $request->only([
                'userName',
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
