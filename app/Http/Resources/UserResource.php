<?php

namespace App\Http\Resources;

use App\Enums\HttpStatusCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource;

        return [
            'user' => [
                'email'     => $user->email,
                'gender'    => $user->profile->gender,
                'dob'       => $user->profile->dob,
                'firstName' => $user->profile->first_name,
                'lastName'  => $user->profile->last_name,
                'timezone'  => $user->profile->timezone,
            ],
            'tokens' => [
                'accessToken'  => $user->access_token,
                'refreshToken' => $user->refresh_token,
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setStatusCode(HttpStatusCode::CREATED->value);
    }

    /**
     * {@inheritDoc}
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'statusText' => HttpStatusCode::CREATED->text(),
                'timestamp'  => now()->toDateTimeLocalString(),
            ],
        ];
    }
}
