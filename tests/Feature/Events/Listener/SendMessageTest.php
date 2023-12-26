<?php

namespace Tests\Feature\Events\Listener;

use App\Console\Commands\Notification\SendBirthDayMessageCommand;
use App\Events\SendBirthdayMessage;
use App\Listeners\SendMessage;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use Tests\TestCase;

class SendMessageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_will_call_send_message_api_and_return_birthday_message(): void
    {
        $timezone = 'Asia/Jakarta';
        $today = Date::createFromTime('09', '00', '00', $timezone);
        Date::setTestNow($today);
        $user = User::factory()->create();
        UserProfile::factory()->belongsToUser($user)->create([
            'dob'      => sprintf('1990-%s-%s', $today->month, $today->day),
            'timezone' => $timezone,
        ]);
        $message = sprintf(
            'Birthday message was successfully sent to %s at %s',
            $user->email,
            now($timezone)->toDateTimeLocalString()
        );
        $spyCommand = $this->spy(SendBirthDayMessageCommand::class);
        $event = new SendBirthdayMessage($user, $spyCommand);
        $mockResponse = $this->mock(Response::class, function (MockInterface $mock) use ($today) {
            $mock->shouldReceive('ok')->andReturnTrue();
            $mock->shouldReceive('offsetGet')->with('status')->andReturn('sent');
            $mock->shouldReceive('offsetGet')->with('sentTime')->andReturn($today->toDateTimeLocalString());
        });
        Http::shouldReceive('retry')->withSomeOfArgs(3, 300)->andReturnSelf();
        Http::shouldReceive('post')
            ->with('http://localhost', ['email' => $user->email, 'message' => $event->message()])
            ->andReturn($mockResponse);

        $listener = new SendMessage();
        $listener->handle($event);

        $spyCommand->shouldHaveReceived('info')->with($message)->once();
    }

    /** @test */
    public function it_will_not_call_send_message_api_when_the_hours_is_less_then_9_am(): void
    {
        $timezone = 'Asia/Jakarta';
        $pastDate = Date::createFromTime('08', '00', '00', $timezone);
        Date::setTestNow($pastDate);
        $today = now($timezone);
        $user = User::factory()->create();
        UserProfile::factory()->belongsToUser($user)->create([
            'dob'      => sprintf('1990-%s-%s', $today->month, $today->day),
            'timezone' => $timezone,
        ]);
        $spyCommand = $this->spy(SendBirthDayMessageCommand::class);
        $event = new SendBirthdayMessage($user, $spyCommand);

        $listener = new SendMessage();
        $listener->handle($event);

        Http::assertNothingSent();
        $spyCommand->shouldNotHaveReceived('info');
    }

    /** @test */
    public function it_will_log_error_message_api_when_try_to_send_birthday_message(): void
    {
        $timezone = 'Asia/Jakarta';
        $today = Date::createFromTime('09', '00', '00', $timezone);
        Date::setTestNow($today);
        $user = User::factory()->create();
        UserProfile::factory()->belongsToUser($user)->create([
            'dob'      => sprintf('1990-%s-%s', $today->month, $today->day),
            'timezone' => $timezone,
        ]);
        $message = sprintf(
            'Birthday message was successfully sent to %s at %s',
            $user->email,
            now($timezone)->toDateTimeLocalString()
        );
        $spyCommand = $this->spy(SendBirthDayMessageCommand::class);
        $event = new SendBirthdayMessage($user, $spyCommand);
        $mockResponse = $this->mock(Response::class, function (MockInterface $mock) use ($today) {
            $mock->shouldReceive('ok')->andReturnFalse();
            $mock->shouldReceive('status')->andReturn(500);
            $mock->shouldNotReceive('offsetGet')->with('status')->andReturn('sent');
            $mock->shouldReceive('offsetGet')->with('sentTime')->andReturn($today->toDateTimeLocalString());
        });
        Http::shouldReceive('retry')->withSomeOfArgs(3, 300)->andReturnSelf();
        Http::shouldReceive('post')
            ->with('http://localhost', ['email' => $user->email, 'message' => $event->message()])
            ->andReturn($mockResponse);
        $listener = new SendMessage();
        Log::shouldReceive('error')->with("Cannot sent birthday message to {$user->email}", [
            'event'      => $event,
            'listener'   => $listener,
            'statusCode' => 500,
        ])->once();

        $listener->handle($event);

        $spyCommand->shouldNotHaveReceived('info');
    }
}
