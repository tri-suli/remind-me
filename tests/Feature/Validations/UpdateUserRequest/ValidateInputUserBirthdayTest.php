<?php

namespace Feature\Validations\UpdateUserRequest;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ValidateInputUserBirthdayTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_should_not_has_error_input_user_birthday_when_the_value_is_unset(): void
    {
        Date::setTestNow();
        $user = User::factory()->create();
        UserProfile::factory()->belongsToUser($user)->create();
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', ['id' => $user->id]), [
            'email'     => $this->faker->email,
            'password'  => $this->faker->password(11),
            'firstName' => $this->faker->firstName,
            'lastName'  => $this->faker->lastName,
            'location'  => $this->faker->timezone,
        ]);

        $response
            ->assertOk()
            ->assertJsonMissingPath('data.errors');
    }

    /**
     * @dataProvider invalidBirthdayValueDataProvider
     *
     * @test
     *
     * @param  string  $dobFormat
     */
    public function it_has_error_input_user_birthday_when_the_value_format_is_not_y_m_d(string $dobFormat): void
    {
        Date::setTestNow();
        $user = User::factory()->create();
        UserProfile::factory()->belongsToUser($user)->create();
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', ['id' => $user->id]), [
            'dob' => $this->faker->date($dobFormat),
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
                    'timestamp'  => Date::now()->toDateTimeLocalString(),
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
