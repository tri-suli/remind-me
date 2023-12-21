<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Console\Command;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendBirthdayMessage implements Notification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The user that will receive the birthday message
     *
     * @var User
     */
    public readonly User $user;

    /**
     * The artisan console command instance
     *
     * @var Command
     */
    public readonly Command $console;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Command $console)
    {
        $this->user = $user;
        $this->console = $console;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function message(): string
    {
        return sprintf('Hey, %s it’s your birthday', $this->user->profile->full_name);
    }
}
