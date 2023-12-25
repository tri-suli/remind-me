<?php

namespace App\Http\Resources;

use App\Enums\HttpStatusCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource implements DynamicStatusCode
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource;
        $data = [
            'user' => [
                'userName'  => $user->name,
                'email'     => $user->email,
                'firstName' => $user->profile->first_name,
                'lastName'  => $user->profile->last_name,
                'gender'    => $user->profile->gender,
                'dob'       => $user->profile->dob,
                'location'  => $user->profile->timezone,
            ],
        ];

        if ($this->getStatusCode($request) === HttpStatusCode::CREATED || $request->routeIs('api.login')) {
            $data['tokens'] = [
                'accessToken'  => $user->access_token,
                'refreshToken' => $user->refresh_token,
            ];
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setStatusCode($this->getStatusCode($request)->value);
    }

    /**
     * {@inheritDoc}
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'statusText' => $this->getStatusCode($request)->text(),
                'timestamp'  => now()->toDateTimeLocalString(),
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode(Request $request): HttpStatusCode
    {
        if ($request->routeIs('api.user.register')) {
            return HttpStatusCode::CREATED;
        } elseif ($request->routeIs('api.user.update') || $request->routeIs('api.login')) {
            return HttpStatusCode::OK;
        }

        return HttpStatusCode::ERROR_SERVER;
    }
}
