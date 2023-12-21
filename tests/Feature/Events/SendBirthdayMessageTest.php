<?php

namespace Tests\Feature\Events;

use App\Console\Commands\Notification\SendBirthDayMessageCommand;
use App\Events\SendBirthdayMessage;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendBirthdayMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_implement_notification_contract(): void
    {
        $user = User::factory()->create();
        $userProfile = UserProfile::factory()->belongsToUser($user)->create();
        $mockCommand = $this->mock(SendBirthDayMessageCommand::class);

        $event = new SendBirthdayMessage($user, $mockCommand);

        $this->assertEquals($user->id, $event->user->id);
        $this->assertEquals("Hey, {$userProfile->full_name} it’s your birthday", $event->message());
    }
}
