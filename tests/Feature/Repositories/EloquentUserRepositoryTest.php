<?php

namespace Tests\Feature\Repositories;

use App\Enums\Gender;
use App\Models\User;
use App\Models\UserProfile;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
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
        $userToken = $this->mock(NewAccessToken::class, function (MockInterface $mock) {
            $mock->plainTextToken = 'secret-access-token';
        });
        $user = $this->mock(User::class, function (MockInterface $mock) use ($userToken) {
            $mock->shouldReceive('createToken')
                ->andReturn($userToken);
        });

        $result = $this->repository->createAccessToken($user);

        $this->assertEquals('secret-access-token', $result);
    }

    /** @test */
    public function create_user_refresh_token(): void
    {
        $userToken = $this->mock(NewAccessToken::class, function (MockInterface $mock) {
            $mock->plainTextToken = 'secret-refresh-token';
        });
        $user = $this->mock(User::class, function (MockInterface $mock) use ($userToken) {
            $mock->shouldReceive('createToken')
                ->andReturn($userToken);
        });

        $result = $this->repository->createAccessToken($user);

        $this->assertEquals('secret-refresh-token', $result);
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
            'location'  => $this->faker->timezone,
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
            'timezone'   => $userProfile['location'],
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

    /** @test */
    public function should_receive_users_that_have_birthday_in_current_date(): void
    {
        Date::setTestNow();
        $day = Date::now()->format('d');
        $month = Date::now()->format('m');

        $user1 = User::factory()->create();
        UserProfile::factory()->belongsToUser($user1)->create([
            'dob' => sprintf('1995-%s-%s', $month, $day),
        ]);
        $user2 = User::factory()->create();
        UserProfile::factory()->belongsToUser($user2)->create([
            'dob' => sprintf('1990-%s-%s', $month, $day),
        ]);
        $user3 = User::factory()->create();
        UserProfile::factory()->belongsToUser($user3)->create([
            'dob' => '1995-08-12',
        ]);

        $result = $this->repository->findBirthdayNow();

        $this->assertCount(2, $result);
        $this->assertEquals([
            [
                ...$user1->only('id', 'email', 'name'),
                'profile' => $user1->profile->only(['id', 'user_id', 'first_name', 'last_name', 'dob', 'gender', 'timezone']),
            ],
            [
                ...$user2->only('id', 'email', 'name'),
                'profile' => $user2->profile->only(['id', 'user_id', 'first_name', 'last_name', 'dob', 'gender', 'timezone']),
            ],
        ], $result->toArray());
    }

    /** @test */
    public function can_update_user_n_user_profile_record_by_user_id(): void
    {
        $user = User::factory()->create();
        $userProfile = UserProfile::factory()->belongsToUser($user)->create([
            'gender'   => Gender::FEMALE->value,
            'timezone' => 'Asia/Kuala_Lumpur',
        ]);

        $result = $this->repository->updateWithProfile($user->id, [
            'userName'  => $newUserName = 'username',
            'email'     => $newEmail = 'user@mail.com',
            'firstName' => $newFirstName = 'First Name',
            'lastName'  => $newLastName = 'Last Name',
            'dob'       => $newDob = '1990-01-12',
            'gender'    => $newGender = Gender::MALE->value,
            'location'  => $newLocation = 'Asia/Jakarta',
        ]);

        $this->assertEquals($user->id, $result->id);
        $this->assertDatabaseMissing('users', [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ]);
        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => $newUserName,
            'email' => $newEmail,
        ]);
        $this->assertDatabaseMissing('user_profiles', [
            'id'         => $userProfile->id,
            'first_name' => $userProfile->first_name,
            'last_name'  => $userProfile->last_name,
            'dob'        => $userProfile->dob,
            'gender'     => $userProfile->gender,
            'timezone'   => $userProfile->timezone,
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'id'         => $userProfile->id,
            'first_name' => $newFirstName,
            'last_name'  => $newLastName,
            'dob'        => $newDob,
            'gender'     => $newGender,
            'timezone'   => $newLocation,
        ]);
    }

    /** @test */
    public function update_with_profile_can_only_update_user_profile_by_user_id(): void
    {
        $user = User::factory()->create();
        $userProfile = UserProfile::factory()->belongsToUser($user)->create([
            'gender'   => Gender::FEMALE->value,
            'timezone' => 'Asia/Kuala_Lumpur',
        ]);

        $result = $this->repository->updateWithProfile($user->id, [
            'firstName' => $newFirstName = 'First Name',
            'lastName'  => $newLastName = 'Last Name',
            'dob'       => $newDob = '1990-01-12',
            'gender'    => $newGender = Gender::MALE->value,
            'location'  => $newLocation = 'Asia/Jakarta',
        ]);

        $this->assertEquals($user->id, $result->id);
        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ]);
        $this->assertDatabaseMissing('user_profiles', [
            'id'         => $userProfile->id,
            'first_name' => $userProfile->first_name,
            'last_name'  => $userProfile->last_name,
            'dob'        => $userProfile->dob,
            'gender'     => $userProfile->gender,
            'timezone'   => $userProfile->timezone,
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'id'         => $userProfile->id,
            'first_name' => $newFirstName,
            'last_name'  => $newLastName,
            'dob'        => $newDob,
            'gender'     => $newGender,
            'timezone'   => $newLocation,
        ]);
    }

    /** @test */
    public function update_with_profile_can_only_update_user_by_user_id(): void
    {
        $user = User::factory()->create();
        $userProfile = UserProfile::factory()->belongsToUser($user)->create([
            'gender'   => Gender::FEMALE->value,
            'timezone' => 'Asia/Kuala_Lumpur',
        ]);

        $result = $this->repository->updateWithProfile($user->id, [
            'userName' => $newUserName = 'username',
            'email'    => $newUserEmail = 'username@mail.com',
        ]);

        $this->assertEquals($user->id, $result->id);
        $this->assertDatabaseMissing('users', [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ]);
        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => $newUserName,
            'email' => $newUserEmail,
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'id'         => $userProfile->id,
            'first_name' => $userProfile->first_name,
            'last_name'  => $userProfile->last_name,
            'dob'        => $userProfile->dob,
            'gender'     => $userProfile->gender,
            'timezone'   => $userProfile->timezone,
        ]);
    }
}
