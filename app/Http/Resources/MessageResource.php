<?php

namespace App\Http\Resources;

use App\Enums\HttpStatusCode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->resource,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'statusText' => HttpStatusCode::OK->text(),
                'timestamp'  => now()->toDateTimeLocalString(),
            ],
        ];
    }
}
