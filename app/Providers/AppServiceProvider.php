<?php

namespace App\Providers;

use App\Console\Commands\Notification\SendBirthDayMessageCommand;
use App\Http\Controllers\Service\User\DeleteController;
use App\Http\Controllers\Service\User\RegisterController;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\EloquentRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->when(RegisterController::class)
            ->needs(EloquentRepository::class)
            ->give(EloquentUserRepository::class);

        $this->app->when(DeleteController::class)
            ->needs(EloquentRepository::class)
            ->give(EloquentUserRepository::class);

        $this->app->when(SendBirthDayMessageCommand::class)
            ->needs(EloquentRepository::class)
            ->give(EloquentUserRepository::class);
    }
}
