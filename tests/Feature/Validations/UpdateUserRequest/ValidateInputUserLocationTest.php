<?php

namespace Feature\Validations\UpdateUserRequest;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ValidateInputUserLocationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_should_not_has_error_input_location_when_value_is_unset(): void
    {
        $today = Date::now();
        Date::setTestNow($today);
        $user = User::factory()->create();
        UserProfile::factory()->belongsToUser($user)->create();
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', ['id' => $user->id]), [
            'email' => $this->faker->email,
        ]);

        $response
            ->assertOk()
            ->assertJsonMissingPath('data.errors');
    }

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
        $user = User::factory()->create();
        UserProfile::factory()->belongsToUser($user)->create();
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', ['id' => $user->id]), [
            'location' => $locationFormat,
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
