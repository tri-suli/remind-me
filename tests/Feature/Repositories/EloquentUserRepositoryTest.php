<?php

namespace Tests\Feature\Repositories;

use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EloquentUserRepositoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private EloquentUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentUserRepository();
    }

    /** @test */
    public function create_a_new_user(): void
    {
        $user = [
            'name'     => $this->faker->userName,
            'email'    => $this->faker->email,
            'password' => '123456',
        ];

        $result = $this->repository->create($user);

        $this->assertInstanceOf(User::class, $result);
        $this->assertDatabaseHas('users', [
            'email' => $user['email'],
            'name'  => $user['name'],
        ]);
    }

    /** @test */
    public function create_user_profile(): void
    {
        $user = User::factory()->create();
        $profile = [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'dob'        => $this->faker->date(),
            'gender'     => 1,
            'timezone'   => $this->faker->timezone,
        ];

        $result = $this->repository->createProfile($user, $profile);

        $this->assertInstanceOf(User::class, $result);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            ...$profile,
        ]);
    }

    /** @test */
    public function find_a_user_by_email(): void
    {
        $user = User::factory()->create();

        $result = $this->repository->firstByEmail($user->email);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    /** @test */
    public function find_a_user_by_email_should_return_null(): void
    {
        $result = $this->repository->firstByEmail($this->faker->email);

        $this->assertNull($result);
    }

    /** @test */
    public function create_user_with_profile(): void
    {
        $profile = [
            'firstName' => $this->faker->firstName,
            'lastName'  => $this->faker->lastName,
            'dob'       => $this->faker->date(),
            'gender'    => 1,
            'timezone'  => $this->faker->timezone,
        ];
        $user = [
            'email'    => $this->faker->email,
            'password' => '123456',
        ];

        $result = $this->repository->register([...$user, ...$profile]);

        $this->assertInstanceOf(User::class, $result);
        $this->assertDatabaseHas('users', [
            'name'  => $user['email'],
            'email' => $user['email'],
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'user_id'    => $result->id,
            'first_name' => $profile['firstName'],
            'last_name'  => $profile['lastName'],
            'dob'        => $profile['dob'],
            'gender'     => 1,
            'timezone'   => $profile['timezone'],
        ]);
    }
}
