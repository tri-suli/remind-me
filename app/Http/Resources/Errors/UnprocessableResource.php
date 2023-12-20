<?php

namespace App\Http\Resources\Errors;

use App\Enums\HttpStatusCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\ValidationException;

class UnprocessableResource extends JsonResource
{
    /**
     * {@inheritDoc}
     *
     * @param  ValidationException  $resource
     */
    public function __construct(ValidationException $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'errors' => $this->resource->errors(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setStatusCode(HttpStatusCode::INVALID_ENTITY->value);
    }

    /**
     * {@inheritDoc}
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'statusText' => HttpStatusCode::INVALID_ENTITY->text(),
                'timestamp'  => Date::now()->toDateTimeLocalString(),
            ],
        ];
    }
}
