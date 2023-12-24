<?php

namespace Tests\Unit\Http\Resources;

use App\Http\Resources\UserResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;

class UserResourceTest extends TestCase
{
    /** @test */
    public function it_will_return_http_enum_200(): void
    {
        Mockery::mock(Carbon::class);
        $mockRequest = Mockery::spy(Request::class);
        $mockRequest->shouldReceive('routeIs')->with('api.user.update')->andReturnTrue();
        $mockResponse = Mockery::spy(JsonResponse::class);
        $resource = new UserResource([]);

        $resource->withResponse($mockRequest, $mockResponse);

        $this->assertEquals('ok', $resource->with($mockRequest)['meta']['statusText']);
        $mockResponse->shouldHaveReceived('setStatusCode')->with(200)->once();
    }

    /** @test */
    public function it_will_return_http_enum_201(): void
    {
        Mockery::mock(Carbon::class);
        $mockRequest = Mockery::spy(Request::class);
        $mockRequest->shouldReceive('routeIs')->with('api.user.register')->andReturnTrue();
        $mockResponse = Mockery::spy(JsonResponse::class);
        $resource = new UserResource([]);

        $resource->withResponse($mockRequest, $mockResponse);

        $this->assertEquals('created', $resource->with($mockRequest)['meta']['statusText']);
        $mockResponse->shouldHaveReceived('setStatusCode')->with(201)->once();
    }

    /** @test */
    public function it_will_return_http_enum_500(): void
    {
        Mockery::mock(Carbon::class);
        $mockRequest = Mockery::spy(Request::class);
        $mockRequest->shouldReceive('routeIs')->withAnyArgs()->andReturnFalse();
        $mockResponse = Mockery::spy(JsonResponse::class);
        $resource = new UserResource([]);

        $resource->withResponse($mockRequest, $mockResponse);

        $this->assertEquals('internal server error', $resource->with($mockRequest)['meta']['statusText']);
        $mockResponse->shouldHaveReceived('setStatusCode')->with(500)->once();
    }
}
