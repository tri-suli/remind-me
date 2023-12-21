<?php

namespace App\Listeners;

use App\Events\Notification;
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
        $userEmail = $event->user->email;
        $today = now($event->user->profile->timezone);

        if ($today->hour >= 9) {
            $response = Http::post(config('api.external.birthday'), [
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
        }
    }
}
