<?php

namespace Feature\Validations\StoreUserRequest;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ValidateInputUserLocationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @dataProvider invalidLocationValueDataProvider
     *
     * @test
     *
     * @param  string  $locationFormat
     */
    public function it_has_error_input_user_location_when_the_value_incorrect_timezone(string $locationFormat): void
    {
        $today = Date::now();
        Date::setTestNow($today);

        $response = $this->postJson(route('api.user.register'), [
            'email'     => $this->faker->email,
            'password'  => $this->faker->password(11),
            'firstName' => $this->faker->firstName,
            'lastName'  => $this->faker->lastName,
            'gender'    => 1,
            'dob'       => $this->faker->date(),
            'location'  => $locationFormat,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJson([
                'data' => [
                    'errors' => [
                        'location' => [
                            __('validation.timezone', ['attribute' => 'location']),
                        ],
                    ],
                ],
                'meta' => [
                    'statusText' => 'unprocessable entity',
                    'timestamp'  => $today->toDateTimeLocalString(),
                ],
            ])
            ->assertJsonMissingPath('data.errors.userName')
            ->assertJsonMissingPath('data.errors.firstName')
            ->assertJsonMissingPath('data.errors.lastName')
            ->assertJsonMissingPath('data.errors.email')
            ->assertJsonMissingPath('data.errors.password')
            ->assertJsonMissingPath('data.errors.dob')
            ->assertJsonMissingPath('data.errors.gender');
    }

    /**
     * Get invalid location value
     *
     * @return array
     */
    public static function invalidLocationValueDataProvider(): array
    {
        return [
            'using invalid timezone code' => ['ABC'],
            'using continent only'        => ['Asia'],
            'using city only'             => ['Jakarta'],
        ];
    }
}
