<?php

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\Errors\UnAuthenticatedResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class ErrorUnAuthenticatedResourceTest extends TestCase
{
    /** @test */
    public function it_should_contains_response_422(): void
    {
        $mockRequest = Mockery::mock(Request::class);
        $mockJsonResource = Mockery::spy(JsonResponse::class);
        $resource = new UnAuthenticatedResource();
        $resource->withResponse($mockRequest, $mockJsonResource);

        $this->assertEquals([
            'message' => 'Your\'re not authorized to access this endpoint!',
        ], $resource->toArray($mockRequest));
        $mockJsonResource->shouldHaveReceived('setStatusCode')->with(401)->once();
    }
}
