<?php

namespace Feature\Validations\UpdateUserRequest;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ValidateInputUserFirstNameTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_should_not_has_error_input_required_user_first_name(): void
    {
        $today = Date::now();
        Date::setTestNow($today);
        $user = User::factory()->create();
        UserProfile::factory()->belongsToUser($user)->create();
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', ['id' => $user->id]), [
            'userName' => $this->faker->userName,
            'email'    => $this->faker->email,
            'password' => $this->faker->password(11, 12),
            'lastName' => $this->faker->lastName,
            'dob'      => $this->faker->date(),
            'location' => $this->faker->timezone,
        ]);

        $response
            ->assertOk()
            ->assertJsonMissingPath('data.errors');
    }

    /** @test */
    public function it_has_error_input_max_length_user_first_name(): void
    {
        $today = Date::now();
        Date::setTestNow($today);
        $user = User::factory()->create();
        UserProfile::factory()->belongsToUser($user)->create();
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', ['id' => $user->id]), [
            'firstName' => str_repeat('john lark man', 11),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJson([
                'data' => [
                    'errors' => [
                        'firstName' => [
                            __('validation.max.string', ['attribute' => 'first name', 'max' => 100]),
                        ],
                    ],
                ],
                'meta' => [
                    'statusText' => 'unprocessable entity',
                    'timestamp'  => $today->toDateTimeLocalString(),
                ],
            ])
            ->assertJsonMissingPath('data.errors.userName')
            ->assertJsonMissingPath('data.errors.email')
            ->assertJsonMissingPath('data.errors.password')
            ->assertJsonMissingPath('data.errors.lastName')
            ->assertJsonMissingPath('data.errors.gender')
            ->assertJsonMissingPath('data.errors.dob')
            ->assertJsonMissingPath('data.errors.location');
    }
}
