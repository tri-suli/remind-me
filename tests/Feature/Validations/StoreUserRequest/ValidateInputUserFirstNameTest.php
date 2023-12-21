<?php

namespace Feature\Validations\StoreUserRequest;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ValidateInputUserFirstNameTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_has_error_input_required_user_first_name(): void
    {
        Date::setTestNow();

        $response = $this->postJson(route('api.user.register'), [
            'userName' => $this->faker->userName,
            'email'    => $this->faker->email,
            'password' => $this->faker->password(11, 12),
            'lastName' => $this->faker->lastName,
            'dob'      => $this->faker->date(),
            'location' => $this->faker->timezone,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJson([
                'data' => [
                    'errors' => [
                        'firstName' => [
                            __('validation.required', ['attribute' => 'first name']),
                        ],
                    ],
                ],
                'meta' => [
                    'statusText' => 'unprocessable entity',
                    'timestamp'  => now()->toDateTimeLocalString(),
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

    /** @test */
    public function it_has_error_input_max_length_user_first_name(): void
    {
        Date::setTestNow();

        $response = $this->postJson(route('api.user.register'), [
            'userName'  => $this->faker->userName,
            'email'     => $this->faker->email,
            'password'  => $this->faker->password(11, 12),
            'firstName' => str_repeat('john lark man', 11),
            'lastName'  => $this->faker->lastName,
            'dob'       => $this->faker->date(),
            'location'  => $this->faker->timezone,
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
                    'timestamp'  => now()->toDateTimeLocalString(),
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
