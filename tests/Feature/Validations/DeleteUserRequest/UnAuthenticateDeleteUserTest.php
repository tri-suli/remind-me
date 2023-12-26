<?php

namespace Tests\Feature\Validations\DeleteUserRequest;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UnAuthenticateDeleteUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function should_receive_un_authenticated_response_when_no_access_token_provide(): void
    {
        $today = Date::now();
        Date::setTestNow($today);
        User::factory()->create();

        $response = $this->deleteJson(route('api.user.delete'));

        $response
            ->assertUnauthorized()
            ->assertJsonPath('data', ['message' => 'Your\'re not authorized to access this endpoint!'])
            ->assertJsonPath('meta', [
                'statusText' => 'unauthenticated/unauthorized',
                'timestamp'  => $today->toDateTimeLocalString(),
            ]);
    }

    /** @test */
    public function should_receive_un_authenticated_response_when_wrong_access_token_name_provided(): void
    {
        Date::setTestNow();
        Sanctum::actingAs(User::factory()->create(), ['not-access-api']);

        $response = $this->deleteJson(route('api.user.delete'));

        $response
            ->assertUnauthorized()
            ->assertJsonPath('data', ['message' => 'Your\'re not authorized to access this endpoint!'])
            ->assertJsonPath('meta', [
                'statusText' => 'unauthenticated/unauthorized',
                'timestamp'  => now()->toDateTimeLocalString(),
            ]);
    }
}
