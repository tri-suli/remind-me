<?php

use App\Http\Controllers\Service\User;
use App\Http\Controllers\Service\UserController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->name('api.')->group(function (Router $router) {
    $router->post('login', User\LoginController::class)->name('login');

    Route::prefix('user')->name('user.')->group(function (Router $router) {
        $router->post('', User\RegisterController::class)->name('register');
        $router->delete('', User\DeleteController::class)->name('delete');
        $router->patch('{id}', [UserController::class, 'update'])->name('update');
    });
});
