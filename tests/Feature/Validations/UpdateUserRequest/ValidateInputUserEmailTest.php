<?php

namespace Feature\Validations\UpdateUserRequest;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ValidateInputUserEmailTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_should_not_receive_error_input_unique_user_email_if_the_value_is_similar_with_existing_user_email(): void
    {
        Date::setTestNow();
        $user = User::factory()->create(['email' => $this->faker->email]);
        UserProfile::factory()->belongsToUser($user)->create();
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', ['id' => $user->id]), [
            'email' => $user->email,
        ]);

        $response
            ->assertOk()
            ->assertJsonMissingPath('data.errors');
    }

    /** @test */
    public function it_has_error_input_unique_user_email(): void
    {
        Date::setTestNow();
        $user = User::factory()->create(['email' => 'john@mail.com']);
        User::factory()->create(['email' => 'johndoe@mail.com']);
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', $user->id), [
            'email' => 'johndoe@mail.com',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJson([
                'data' => [
                    'errors' => [
                        'email' => [
                            __('validation.unique', ['attribute' => 'email']),
                        ],
                    ],
                ],
                'meta' => [
                    'statusText' => 'unprocessable entity',
                    'timestamp'  => now()->toDateTimeLocalString(),
                ],
            ]);
    }
}
