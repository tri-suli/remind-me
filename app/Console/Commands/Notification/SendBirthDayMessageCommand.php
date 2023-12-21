<?php

namespace App\Console\Commands\Notification;

use App\Events\SendBirthdayMessage;
use App\Models\User;
use App\Repositories\EloquentRepository;
use Illuminate\Console\Command;

class SendBirthDayMessageCommand extends Command
{
    /**
     * Eloquent user repository instance
     *
     * @var EloquentRepository
     */
    public readonly EloquentRepository $eloquentRepository;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:birthday {email? : The user email that will receive the message. If not email specified, System will send birthday messages to all users based on user\'s date of birth and timezone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send the birthday message to the user base on user\'s timezone';

    /**
     * Create a new command instance
     *
     * @param  EloquentRepository  $eloquentRepository
     */
    public function __construct(EloquentRepository $eloquentRepository)
    {
        parent::__construct();
        $this->eloquentRepository = $eloquentRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $email = $this->argument('email');

        if ($email) {
            $user = $this->eloquentRepository->firstByEmail($email);

            if (($user instanceof User)) {
                event(new SendBirthdayMessage($user, $this));
            } else {
                $this->error(sprintf('Cannot find user where email is %s', $email));
            }
        } else {
            $this->eloquentRepository->findBirthdayNow()->each(function (User $user) {
                event(new SendBirthdayMessage($user, $this));
            });
        }
    }
}
