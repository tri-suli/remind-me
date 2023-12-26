<?php

namespace Tests\Unit\Http\Resources;

use App\Exceptions\CredentialMismatchException;
use App\Http\Resources\Errors\BadRequestResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mockery;
use PHPUnit\Framework\TestCase;
use Tests\CreatesApplication;

class ErrorBadRequestResourceTest extends TestCase
{
    use CreatesApplication;

    /** @test */
    public function it_should_have_array_key_message(): void
    {
        $this->createApplication();
        $mockRequest = Mockery::mock(Request::class);
        $mockResponse = Mockery::spy(JsonResponse::class);
        $resourceValue = new CredentialMismatchException();
        $resource = new BadRequestResource($resourceValue);

        $data = $resource->toArray($mockRequest);
        $additionalData = $resource->with($mockRequest);
        $resource->withResponse($mockRequest, $mockResponse);

        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('meta', $additionalData);
        $this->assertArrayHasKey('statusText', $additionalData['meta']);
        $this->assertEquals($resourceValue->getMessage(), $data['message']);
        $this->assertEquals('bad request', $additionalData['meta']['statusText']);
        $mockResponse->shouldHaveReceived('setStatusCode')->with(400)->once();
    }
}
