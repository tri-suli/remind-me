<?php

namespace Feature\Validations\StoreUserRequest;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ValidateInputUserPasswordTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_has_error_input_required_user_password(): void
    {
        $today = Date::now();
        Date::setTestNow($today);

        $response = $this->postJson(route('api.user.register'), [
            'userName'  => $this->faker->userName,
            'email'     => $this->faker->email,
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
                        'password' => [
                            __('validation.required', ['attribute' => 'password']),
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
            ->assertJsonMissingPath('data.errors.firstName')
            ->assertJsonMissingPath('data.errors.lastName')
            ->assertJsonMissingPath('data.errors.gender')
            ->assertJsonMissingPath('data.errors.dob')
            ->assertJsonMissingPath('data.errors.location');
    }

    /** @test */
    public function it_has_error_input_min_length_user_password(): void
    {
        $today = Date::now();
        Date::setTestNow($today);

        $response = $this->postJson(route('api.user.register'), [
            'userName'  => $this->faker->userName,
            'email'     => $this->faker->email,
            'password'  => $this->faker->password(6, 8),
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
                        'password' => [
                            __('validation.min.string', ['attribute' => 'password', 'min' => 11]),
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
            ->assertJsonMissingPath('data.errors.firstName')
            ->assertJsonMissingPath('data.errors.lastName')
            ->assertJsonMissingPath('data.errors.gender')
            ->assertJsonMissingPath('data.errors.dob')
            ->assertJsonMissingPath('data.errors.location');
    }
}
