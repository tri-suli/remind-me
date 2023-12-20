<?php

namespace App\Http\Resources\Errors;

use App\Enums\HttpStatusCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnAuthenticatedResource extends JsonResource
{
    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct('Your\'re not authorized to access this endpoint!');
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return ['message' => $this->resource];
    }

    /**
     * {@inheritDoc}
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setStatusCode(HttpStatusCode::UN_AUTHENTICATED->value);
    }

    /**
     * {@inheritDoc}
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'statusText' => HttpStatusCode::UN_AUTHENTICATED->text(),
                'timestamp'  => now()->toDateTimeLocalString(),
            ],
        ];
    }
}
