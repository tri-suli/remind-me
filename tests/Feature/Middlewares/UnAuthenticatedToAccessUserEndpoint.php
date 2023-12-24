<?php

namespace Tests\Feature\Middlewares;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UnAuthenticatedToAccessUserEndpoint extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @dataProvider routeDataProvider
     */
    public function should_receive_un_authenticated_response_when_no_access_token_provide(string $actionName): void
    {
        Date::setTestNow();
        $user = User::factory()->create();

        if ($actionName === 'update') {
            $response = $this->patchJson(route("api.user.$actionName", $user->id));
        } else {
            $response = $this->deleteJson(route('api.user.delete'));
        }

        $response
            ->assertUnauthorized()
            ->assertJsonPath('data', ['message' => 'Your\'re not authorized to access this endpoint!'])
            ->assertJsonPath('meta', [
                'statusText' => 'unauthenticated/unauthorized',
                'timestamp'  => now()->toDateTimeLocalString(),
            ]);
    }

    /**
     * @test
     *
     * @dataProvider routeDataProvider
     */
    public function should_receive_un_authenticated_response_when_wrong_access_token_name_provided(string $actionName): void
    {
        Date::setTestNow();
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['not-access-api']);

        if ($actionName === 'update') {
            $response = $this->patchJson(route("api.user.$actionName", $user->id));
        } else {
            $response = $this->deleteJson(route('api.user.delete'));
        }

        $response
            ->assertUnauthorized()
            ->assertJsonPath('data', ['message' => 'Your\'re not authorized to access this endpoint!'])
            ->assertJsonPath('meta', [
                'statusText' => 'unauthenticated/unauthorized',
                'timestamp'  => now()->toDateTimeLocalString(),
            ]);
    }

    /**
     * Get user routes name's
     *
     * @return array[]
     */
    public static function routeDataProvider(): array
    {
        return [
            'delete user' => ['delete'],
            'update user' => ['update'],
        ];
    }
}
