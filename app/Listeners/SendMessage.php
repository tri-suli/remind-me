<?php

namespace App\Listeners;

use App\Events\Notification;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendMessage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Notification $event): void
    {
        $timezone = $event->user->profile->timezone;
        $userEmail = $event->user->email;
        $today = now($timezone);

        if ($today->hour >= 9) {
            $response = Http::retry(3, 300, function (Exception $exception, PendingRequest $request) {
                return $exception instanceof ConnectionException || $exception instanceof RequestException;
            })->post(config('api.external.birthday'), [
                'email'   => $userEmail,
                'message' => $event->message(),
            ]);

            if ($response->ok()) {
                $status = $response['status'];
                $sentTime = $response['sentTime'];

                $event->console->info(sprintf(
                    'Birthday message was successfully %s to %s at %s',
                    $status,
                    $userEmail,
                    $sentTime
                ));
            } else {
                Log::error(sprintf('Cannot sent birthday message to %s', $userEmail), [
                    'event'      => $event,
                    'listener'   => $this,
                    'statusCode' => $response->status(),
                ]);
            }
        } else {
            $event->console->warn(sprintf('Uh-oh!: Cannot sent the birthday message to %s', $userEmail));
            $event->console->warn(sprintf('Reason: It was still not 9 am in the %s time zone', $timezone));
        }
    }
}
