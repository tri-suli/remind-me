<?php

namespace Feature\Validations\UpdateUserRequest;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ValidateInputUserGenderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_should_not_has_error_input_user_gender_the_value_is_unset(): void
    {
        Date::setTestNow();
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
     * @dataProvider invalidGenderValueDataProvider
     *
     * @test
     *
     * @param  string|int  $gender
     */
    public function it_has_error_input_user_gender_when_the_value_is_not_0_or_1(string|int $gender): void
    {
        Date::setTestNow();
        $user = User::factory()->create();
        UserProfile::factory()->belongsToUser($user)->create();
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', ['id' => $user->id]), [
            'gender' => $gender,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJson([
                'data' => [
                    'errors' => [
                        'gender' => [
                            __('validation.in', ['attribute' => 'gender']),
                        ],
                    ],
                ],
                'meta' => [
                    'statusText' => 'unprocessable entity',
                    'timestamp'  => now()->toDateTimeLocalString(),
                ],
            ])
            ->assertJsonMissingPath('data.errors.userName')
            ->assertJsonMissingPath('data.errors.firstName')
            ->assertJsonMissingPath('data.errors.lastName')
            ->assertJsonMissingPath('data.errors.email')
            ->assertJsonMissingPath('data.errors.password')
            ->assertJsonMissingPath('data.errors.dob')
            ->assertJsonMissingPath('data.errors.location');
    }

    /**
     * Get invalid gender value
     *
     * @return array
     */
    public static function invalidGenderValueDataProvider(): array
    {
        return [
            'using string gender male'      => ['male'],
            'using string gender female'    => ['female'],
            'using string gender man'       => ['man'],
            'using string gender woman'     => ['woman'],
            'using string gender boy'       => ['boy'],
            'using string gender girl'      => ['girl'],
            'using number where not 0 or 1' => [rand(3, 10)],
        ];
    }
}
