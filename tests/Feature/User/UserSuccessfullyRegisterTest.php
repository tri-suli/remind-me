<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class UserSuccessfullyRegisterTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_will_receive_user_detail_with_access_tokens_response(): void
    {
        Date::setTestNow();
        $input = [
            'firstName' => $this->faker->firstName,
            'lastName'  => $this->faker->lastName,
            'email'     => $this->faker->email,
            'gender'    => (string) rand(0, 1),
            'dob'       => $this->faker->date(),
            'password'  => 'secret',
            'timezone'  => $this->faker->timezone,
        ];

        $response = $this->postJson(route('api.user.register'), $input);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user', [
                'email'     => $input['email'],
                'gender'    => $input['gender'],
                'dob'       => $input['dob'],
                'firstName' => $input['firstName'],
                'lastName'  => $input['lastName'],
                'timezone'  => $input['timezone'],
            ])
            ->assertJsonPath('meta', [
                'statusText' => 'created',
                'timestamp'  => now()->toDateTimeLocalString(),
            ]);
        $this->assertNotEmpty(
            json_decode($response->getContent())->data->tokens->accessToken
        );
        $this->assertNotEmpty(
            json_decode($response->getContent())->data->tokens->refreshToken
        );
    }
}
