<?php

namespace App\Http\Resources;

use App\Enums\HttpStatusCode;
use Illuminate\Http\Request;

interface DynamicStatusCode
{
    /**
     * Get the current status code by the given request
     *
     * @param  Request  $request
     * @return HttpStatusCode
     */
    public function getStatusCode(Request $request): HttpStatusCode;
}
