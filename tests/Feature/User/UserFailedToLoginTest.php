<?php

namespace Tests\Feature\User;

use App\Exceptions\CredentialMismatchException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserFailedToLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     *
     * @dataProvider credentialDataProvider
     *
     * @param  array  $attributes User factory attributes
     * @param  array  $body the request body
     */
    public function it_should_receive_400_response_when_the_email_or_password_incorrect(array $attributes, array $body): void
    {
        $today = $this->date();
        User::factory()->create([
            ...$attributes,
            'password' => Hash::make($attributes['password']),
        ]);

        $response = $this->postJson(route('api.login'), $body);

        $response
            ->assertBadRequest()
            ->assertJson([
                'data' => [
                    'message' => $this->exceptionMessage(),
                ],
                'meta' => [
                    'statusText' => 'bad request',
                    'timestamp'  => $today->toDateTimeLocalString(),
                ],
            ]);
    }

    /**
     * @test
     *
     * @dataProvider credentialDataProvider
     *
     * @param  array  $attributes User factory attributes
     */
    public function it_should_receive_400_response_when_the_email_and_password_incorrect(array $attributes): void
    {
        $today = $this->date();
        User::factory()->create([
            ...$attributes,
            'password' => Hash::make($attributes['password']),
        ]);

        $response = $this->postJson(route('api.login'), [
            'email' => 'someone@mail.com', 'password' => 'fakePassword123',
        ]);

        $response
            ->assertBadRequest()
            ->assertJson([
                'data' => [
                    'message' => $this->exceptionMessage(),
                ],
                'meta' => [
                    'statusText' => 'bad request',
                    'timestamp'  => $today->toDateTimeLocalString(),
                ],
            ]);
    }

    /**
     * Get user credential
     *
     * @return array[]
     */
    public static function credentialDataProvider(): array
    {
        return [
            'wrong email' => [
                ['email' => 'johndoe@mail.com', 'password' => 'secret100%'],
                ['email' => 'johnlark@mail.com', 'password' => 'secret100%'],
            ],
            'wrong password' => [
                ['email' => 'johndoe@mail.com', 'password' => 'secret100%'],
                ['email' => 'johndoe@mail.com', 'password' => 'password'],
            ],
        ];
    }

    /**
     * Get current date
     *
     * @return Carbon
     */
    private function date(): Carbon
    {
        $today = Date::now();
        Date::setTestNow($today);

        return $today;
    }

    /**
     * Get the response error message
     *
     * @return string
     */
    private function exceptionMessage(): string
    {
        return (new CredentialMismatchException())->getMessage();
    }
}
