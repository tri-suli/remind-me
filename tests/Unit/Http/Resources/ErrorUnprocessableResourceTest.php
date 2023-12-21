<?php

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\Errors\UnprocessableResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\TestCase;

class ErrorUnprocessableResourceTest extends TestCase
{
    /** @test */
    public function it_should_contains_response_422(): void
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockJsonResource = Mockery::spy(JsonResponse::class);
        $mockValidationException = $this->createMock(ValidationException::class);
        $mockValidationException->method('errors')->willReturn([
            'input' => ['something went wrong'],
        ]);
        $resource = new UnprocessableResource($mockValidationException);
        $resource->withResponse($mockRequest, $mockJsonResource);

        $this->assertEquals([
            'errors' => [
                'input' => ['something went wrong'],
            ],
        ], $resource->toArray($mockRequest));
        $mockJsonResource->shouldHaveReceived('setStatusCode')->with(422)->once();
    }
}
