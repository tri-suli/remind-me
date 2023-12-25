<?php

namespace Feature\Validations\UpdateUserRequest;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ValidateInputUserNameTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_should_not_receive_error_input_unique_user_name_if_the_value_is_similar_with_existing_user_name(): void
    {
        $today = Date::now();
        Date::setTestNow($today);
        $user = User::factory()->create(['name' => $this->faker->userName]);
        UserProfile::factory()->belongsToUser($user)->create();
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', ['id' => $user->id]), [
            'userName' => $user->name,
        ]);

        $response
            ->assertOk()
            ->assertJsonMissingPath('data.errors');
    }

    /** @test */
    public function it_has_error_input_unique_user_name(): void
    {
        $today = Date::now();
        Date::setTestNow($today);
        $user = User::factory()->create(['name' => $this->faker->userName]);
        User::factory()->create(['name' => 'johndoe']);
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', $user->id), [
            'userName' => 'johndoe',
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
            ]);
    }
}
