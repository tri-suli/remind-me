<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserSuccessfullyDeleteTheirOwnDataTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_will_receive_goodbye_message_when_current_user_is_deleted(): void
    {
        Date::setTestNow();
        $user = Sanctum::actingAs(User::factory()->create(), ['access-api']);
        UserProfile::factory()->belongsToUser($user)->create();

        $response = $this->deleteJson(route('api.user.delete'));

        $response
            ->assertOk()
            ->assertJsonPath('data', ['message' => 'Goodbye!'])
            ->assertJsonPath('meta', [
                'statusText' => 'ok',
                'timestamp'  => now()->toDateTimeLocalString(),
            ]);
        $this->assertDatabaseHas('users', [
            ...$user->only('id', 'email', 'name'),
            'deleted_at' => now(),
        ]);
        $this->assertDatabaseHas('user_profiles', [
            'user_id'    => $user->id,
            'deleted_at' => now(),
        ]);
    }
}
