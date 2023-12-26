<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserSuccessfullyUpdateTheirProfileTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function user_can_update_multiple_values()
    {
        $inputs = [
            'userName'  => 'johndoe',
            'email'     => 'johndoe@mail.com',
            'firstName' => 'John',
            'lastName'  => 'Doe',
            'gender'    => 1,
            'dob'       => '1979-01-12',
            'location'  => 'Asia/Jakarta',
        ];
        $user = User::factory()->create([
            'name'  => 'johnwick',
            'email' => 'johnwick@mail.com',
        ]);
        $userProfile = UserProfile::factory()->belongsToUser($user)->create([
            'first_name' => 'Jonathan',
            'last_name'  => 'Wick',
            'dob'        => '1980-12-01',
            'gender'     => null,
            'timezone'   => 'Asia/Kuala_Lumpur',
        ]);
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', $user->id), $inputs);

        $response
            ->assertOk()
            ->assertJsonPath('data.user', $inputs)
            ->assertJsonMissing([
                'userName' => $user->name,
                ...$user->only('email'),
                ...$userProfile->only([
                    'first_name',
                    'last_name',
                    'gender',
                    'dob',
                    'location',
                ]),
            ])
            ->assertJsonMissingPath('data.tokens')
            ->assertJsonPath('meta.statusText', 'ok');
    }

    /**
     * @test
     *
     * @dataProvider userProfileAttribute
     */
    public function user_can_only_update_a_single_value(string $field, string $newValue, array $attribute): void
    {
        $user = User::factory()->create($attribute['user']);
        UserProfile::factory()->belongsToUser($user)->create($attribute['userProfile']);
        Sanctum::actingAs($user, ['access-api']);

        $response = $this->patchJson(route('api.user.update', $user->id), [
            $field => $newValue,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath("data.user.$field", $newValue)
            ->assertJsonMissingPath('data.tokens')
            ->assertJsonPath('meta.statusText', 'ok');
    }

    /**
     * Get user profile attributes to update
     *
     * @return array
     */
    public static function userProfileAttribute(): array
    {
        return [
            'only update user name' => [
                'userName',
                'johndoe',
                ['user' => ['name' => 'jenny'], 'userProfile' => []],
            ],
            'only update user email' => [
                'email',
                'johndoe@mail.com',
                ['user' => ['email' => 'jennywick@mail.com'], 'userProfile' => []],
            ],
            'only update user first name' => [
                'firstName',
                'John',
                ['user' => [], 'userProfile' => ['first_name' => 'Jenny']],
            ],
            'only update user last name' => [
                'lastName',
                'Doe',
                ['user' => [], 'userProfile' => ['last_name' => 'Wick']],
            ],
            'only update user gender' => [
                'gender',
                '1',
                ['user' => [], 'userProfile' => ['gender' => '0']],
            ],
            'only update user dob' => [
                'dob',
                '1985-01-12',
                ['user' => [], 'userProfile' => ['dob' => '1985-12-01']],
            ],
            'only update user location' => [
                'location',
                'Asia/Jakarta',
                ['user' => [], 'userProfile' => ['timezone' => 'Asia/Kuala_Lumpur']],
            ],
        ];
    }
}
