<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Mockery\MockInterface;
use Tests\TestCase;

class UserSuccessfullyLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function should_receive_user_details_with_access_tokens_when_login_successful(): void
    {
        $today = now();
        Date::setTestNow($today);
        $user = $this->createAndPrepareUser();
        $this->mock(AuthService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldIgnoreMissing();
            $mock->shouldReceive('resolveLoginUser')->andReturn($user);
            $mock->shouldReceive('giveTokensToUser')->withArgs(function ($param) use ($user) {
                $user->access_token = 'secret-access-token';
                $user->refresh_token = 'secret-refresh-token';

                return $user->id === $param->id;
            })->andReturns();
        });

        $response = $this->postJson(route('api.login'), ['email' => $user->email, 'password' => 'secret100%'], [
            'device-name' => 'Iphone15',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'user' => [
                        'userName'  => $user->name,
                        'email'     => $user->email,
                        'firstName' => $user->profile->first_name,
                        'lastName'  => $user->profile->last_name,
                        'dob'       => $user->profile->dob,
                        'gender'    => (string) $user->profile->gender,
                        'location'  => $user->profile->timezone,
                    ],
                    'tokens' => [
                        'accessToken'  => 'secret-access-token',
                        'refreshToken' => 'secret-refresh-token',
                    ],
                ],
                'meta' => [
                    'statusText' => 'ok',
                    'timestamp'  => $today->toDateTimeLocalString(),
                ],
            ]);
    }

    /** @test */
    public function it_will_regenerate_user_session_and_return_user_details_with_access_tokens_when_login_successful(): void
    {
        $today = now();
        Date::setTestNow($today);
        $user = $this->createAndPrepareUser();
        $this->mock(AuthService::class, function (MockInterface $mock) use ($user) {
            $mock->shouldIgnoreMissing();
            $mock->shouldAllowMockingProtectedMethods();
            $mock->shouldReceive('isFromThirdPartyRequest')->andReturnFalse();
            $mock->shouldReceive('validateUserCredential')->andReturnTrue();
            $mock->shouldReceive('resolveLoginUser')->andReturn($user);
            $mock->shouldReceive('giveTokensToUser')->withArgs(function ($param) use ($user) {
                $user->access_token = 'secret-access-token';
                $user->refresh_token = 'secret-refresh-token';

                return $user->id === $param->id;
            })->andReturns();
        });

        $response = $this->postJson(route('api.login'), [
            'email'    => $user->email,
            'password' => 'secret100%',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'data' => [
                    'user' => [
                        'userName'  => $user->name,
                        'email'     => $user->email,
                        'firstName' => $user->profile->first_name,
                        'lastName'  => $user->profile->last_name,
                        'dob'       => $user->profile->dob,
                        'gender'    => (string) $user->profile->gender,
                        'location'  => $user->profile->timezone,
                    ],
                    'tokens' => [
                        'accessToken'  => 'secret-access-token',
                        'refreshToken' => 'secret-refresh-token',
                    ],
                ],
                'meta' => [
                    'statusText' => 'ok',
                    'timestamp'  => $today->toDateTimeLocalString(),
                ],
            ]);
    }

    /**
     * Prepare the user
     *
     * @return User
     */
    private function createAndPrepareUser(): User
    {
        $user = User::factory()->create(['password' => 'secret100%']);
        UserProfile::factory()->belongsToUser($user)->create();

        return $user;
    }
}
