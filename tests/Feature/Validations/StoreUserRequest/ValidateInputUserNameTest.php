<?php

namespace Feature\Validations\StoreUserRequest;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ValidateInputUserNameTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_has_error_input_user_name(): void
    {
        $today = Date::now();
        Date::setTestNow($today);
        $username = $this->faker->userName;
        User::factory()->create(['name' => $username]);

        $response = $this->postJson(route('api.user.register'), [
            'userName'  => $username,
            'email'     => $this->faker->email,
            'password'  => $this->faker->password(11),
            'firstName' => $this->faker->firstName,
            'lastName'  => $this->faker->lastName,
            'dob'       => $this->faker->date(),
            'location'  => $this->faker->timezone,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJson([
                'data' => [
                    'errors' => [
                        'userName' => [
                            __('validation.unique', ['attribute' => 'user name']),
                        ],
                    ],
                ],
                'meta' => [
                    'statusText' => 'unprocessable entity',
                    'timestamp'  => $today->toDateTimeLocalString(),
                ],
            ])
            ->assertJsonMissingPath('data.errors.firstName')
            ->assertJsonMissingPath('data.errors.lastName')
            ->assertJsonMissingPath('data.errors.gender')
            ->assertJsonMissingPath('data.errors.dob')
            ->assertJsonMissingPath('data.errors.location');
    }
}
