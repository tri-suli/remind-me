<?php

namespace App\Http\Resources\Errors;

use App\Enums\HttpStatusCode;
use App\Exceptions\CredentialMismatchException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof CredentialMismatchException) {
            return ['message' => $this->resource->getMessage()];
        }

        return parent::toArray($request);
    }

    /**
     * {@inheritDoc}
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setStatusCode(HttpStatusCode::BAD_REQUEST->value);
    }

    /**
     * {@inheritDoc}
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'statusText' => HttpStatusCode::BAD_REQUEST->text(),
                'timestamp'  => now()->toDateTimeLocalString(),
            ],
        ];
    }
}
