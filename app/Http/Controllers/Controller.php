<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Initiate auth sanctum middleware with ability's to access API
     *
     * @return void
     */
    public function needsAccessApiToken(): void
    {
        $this->middleware(['auth:sanctum', 'ability:access-api']);
    }
}
