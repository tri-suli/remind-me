<?php

namespace Feature\Validations\StoreUserRequest;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ValidateInputUserBirthdayTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * @dataProvider invalidBirthdayValueDataProvider
     *
     * @test
     *
     * @param  string  $dobFormat
     */
    public function it_has_error_input_user_birthday_when_the_value_format_is_not_y_m_d(string $dobFormat): void
    {
        $today = Date::now();
        Date::setTestNow($today);

        $response = $this->postJson(route('api.user.register'), [
            'email'     => $this->faker->email,
            'password'  => $this->faker->password(11),
            'firstName' => $this->faker->firstName,
            'lastName'  => $this->faker->lastName,
            'dob'       => $this->faker->date($dobFormat),
            'location'  => $this->faker->timezone,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJson([
                'data' => [
                    'errors' => [
                        'dob' => [
                            __('validation.date_format', ['attribute' => 'date of birth', 'format' => 'Y-m-d']),
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
            ->assertJsonMissingPath('data.errors.gender')
            ->assertJsonMissingPath('data.errors.location');
    }

    /**
     * Get invalid birthday date value
     *
     * @return array
     */
    public static function invalidBirthdayValueDataProvider(): array
    {
        return [
            'using format Ymd'         => ['Ymd'],
            'using format m-Y-d'       => ['m-Y-d'],
            'using format d-m-Y'       => ['d-m-Y'],
            'using format m-d-Y'       => ['m-d-Y'],
            'using format y-m-d H:i'   => ['y-m-d H:i'],
            'using format y-m-d H:i:s' => ['y-m-d H:i:s'],
        ];
    }
}
