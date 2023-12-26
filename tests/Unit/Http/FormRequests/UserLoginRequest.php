<?php

namespace Tests\Unit\Http\FormRequests;

use App\Http\Requests\User\LoginRequest;
use PHPUnit\Framework\TestCase;

class UserLoginRequest extends TestCase
{
    /**
     * The testing subject instance
     *
     * @var LoginRequest
     */
    public readonly LoginRequest $request;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new LoginRequest();
    }

    /** @test */
    public function it_always_authorize_incoming_requests(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    /** @test */
    public function it_will_rules_required_email_n_password(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertEquals(['required'], $rules['email']);
        $this->assertEquals(['required'], $rules['password']);
    }
}
