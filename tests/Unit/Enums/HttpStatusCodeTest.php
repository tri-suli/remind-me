<?php

namespace Tests\Unit\Enums;

use App\Enums\HttpStatusCode;
use PHPUnit\Framework\TestCase;

class HttpStatusCodeTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider httpStatusDataProvider
     */
    public function http_status_code_should_have_status_text(int $code, string $text): void
    {
        $enum = HttpStatusCode::create($code);

        $this->assertEquals($code, $enum->value);
        $this->assertEquals($text, $enum->text());
    }

    public static function httpStatusDataProvider(): array
    {
        return [
            'status code 200' => [200, 'ok'],
            'status code 201' => [201, 'created'],
            'status code 204' => [204, 'no content'],
            'status code 400' => [400, 'bad request'],
            'status code 401' => [401, 'unauthenticated/unauthorized'],
            'status code 403' => [403, 'forbidden'],
            'status code 404' => [404, 'not found'],
            'status code 422' => [422, 'unprocessable entity'],
            'status code 500' => [500, 'internal server error'],
            'status code 502' => [502, 'bad gateway'],
        ];
    }
}
