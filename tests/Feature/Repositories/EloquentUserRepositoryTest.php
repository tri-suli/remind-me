<?php

namespace Tests\Feature\Repositories;

use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\NewAccessToken;
use Mockery\MockInterface;
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
    public function create_user_access_token(): void
    {
        $configMock = $this->spy('config');
        $userToken = $this->mock(NewAccessToken::class, function (MockInterface $mock) {
            $mock->plainTextToken = 'secret-access-token';
        });
        $user = $this->mock(User::class, function (MockInterface $mock) use ($userToken) {
            $mock->shouldReceive('createToken')
                ->andReturn($userToken);
        });

        $result = $this->repository->createAccessToken($user);

        $this->assertEquals('secret-access-token', $result);
        $configMock->shouldHaveReceived('get')->once();
    }

    /** @test */
    public function create_user_refresh_token(): void
    {
        $configMock = $this->spy('config');
        $userToken = $this->mock(NewAccessToken::class, function (MockInterface $mock) {
            $mock->plainTextToken = 'secret-refresh-token';
        });
        $user = $this->mock(User::class, function (MockInterface $mock) use ($userToken) {
            $mock->shouldReceive('createToken')
                ->andReturn($userToken);
        });

        $result = $this->repository->createAccessToken($user);

        $this->assertEquals('secret-refresh-token', $result);
        $configMock->shouldHaveReceived('get')->once();
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
    public function registering_new_user(): void
    {
        $userProfile = [
            'email'     => $this->faker->email,
            'password'  => '12345',
            'firstName' => $this->faker->firstName,
            'lastName'  => $this->faker->lastName,
            'dob'       => $this->faker->date(),
            'gender'    => 1,
            'timezone'  => $this->faker->timezone,
        ];

        $result = $this->repository->register($userProfile);

        $this->assertInstanceOf(User::class, $result);
        $this->assertDatabaseHas('users', [
            'name'  => $userProfile['email'],
            'email' => $userProfile['email'],
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'user_id'    => $result->id,
            'first_name' => $userProfile['firstName'],
            'last_name'  => $userProfile['lastName'],
            'dob'        => $userProfile['dob'],
            'gender'     => $userProfile['gender'],
            'timezone'   => $userProfile['timezone'],
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id'   => $result->id,
            'name'           => 'access_token',
            'abilities'      => json_encode(['access-api']),
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
            'tokenable_id'   => $result->id,
            'name'           => 'refresh_token',
            'abilities'      => json_encode(['issue-access-token']),
        ]);
        $this->assertEquals(50, strlen($result->access_token));
        $this->assertEquals(50, strlen($result->refresh_token));
    }
}
