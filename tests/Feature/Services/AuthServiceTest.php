<?php

namespace Feature\Services;

use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected Request|Mockery\MockInterface|Mockery\LegacyMockInterface $request;

    protected EloquentUserRepository|Mockery\MockInterface|Mockery\LegacyMockInterface $userRepo;

    protected AuthService $authService;

    public function setUp(): void
    {
        parent::setUp();

        $this->userRepo = Mockery::mock(EloquentUserRepository::class);
        $this->request = Mockery::mock(Request::class);

        $this->authService = new AuthService($this->request, $this->userRepo);
    }

    /** @test */
    public function it_will_determine_that_the_request_coming_from_third_party(): void
    {
        $this->request->shouldReceive('hasHeader')->with('device-name')->andReturnTrue();

        $this->assertTrue($this->authService->isFromThirdPartyRequest());
    }

    /** @test */
    public function it_will_determine_that_the_request_coming_spa(): void
    {
        $this->request->shouldReceive('hasHeader')->with('device-name')->andReturnFalse();

        $this->assertFalse($this->authService->isFromThirdPartyRequest());
    }

    /** @test */
    public function should_return_user_model_from_the_request_object()
    {
        $user = User::factory()->create();
        $this->request->shouldReceive('user')->andReturn($user);

        $result = $this->authService->currentUser();

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($result->id, $user->id);
    }

    /** @test */
    public function should_return_user_model_from_generate_by_repository()
    {
        $user = User::factory()->create();
        $this->request->shouldReceive('user')->andReturnNull();
        $this->request->shouldReceive('only')->with('email', 'password')->andReturn([
            'email'    => $user->email,
            'password' => $this->faker->password,
        ]);
        $this->userRepo->shouldReceive('firstByEmail')->with($user->email)->andReturn($user);

        $result = $this->authService->currentUser();

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($result->id, $user->id);
    }

    /** @test */
    public function the_request_object_have_email_n_password_inputs_and_return_them_as_array(): void
    {
        $email = $this->faker->email;
        $password = $this->faker->password;
        $this->request
            ->shouldReceive('only')
            ->with('email', 'password')
            ->andReturn(['email' => $email, 'password' => $password]);

        $result = $this->authService->credential();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('password', $result);
        $this->assertSame($email, $result['email']);
        $this->assertSame($password, $result['password']);
    }

    /** @test */
    public function it_will_receive_empty_credential_when_request_object_doesnt_have_credential_inputs(): void
    {
        $this->request
            ->shouldReceive('only')
            ->with('email', 'password')
            ->andReturn([]);

        $result = $this->authService->credential();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('password', $result);
        $this->assertEmpty($result['email']);
        $this->assertEmpty($result['password']);
    }

    /** @test */
    public function should_give_tokens_to_user()
    {
        $user = User::factory()->create();
        $this->userRepo->shouldReceive('createAccessToken')->with($user)->andReturn('secret-access-token');
        $this->userRepo->shouldReceive('createRefreshToken')->with($user)->andReturn('secret-refresh-token');

        $this->authService->giveTokensToUser($user);

        $this->assertEquals('secret-access-token', $user->access_token);
        $this->assertEquals('secret-refresh-token', $user->refresh_token);
    }

    /** @test */
    public function it_will_return_user_model_when_incoming_login_request_from_third_party_is_success(): void
    {
        $email = $this->faker->email;
        $password = $this->faker->password;
        $user = User::factory()->create(['email' => $email]);
        Hash::shouldReceive('check')->with($password, $user->password)->andReturnTrue();
        $this->request->shouldReceive('hasHeader')->with('device-name')->andReturnTrue();
        $this->request->shouldReceive('user')->andReturnNull();
        $this->userRepo->shouldReceive('firstByEmail')->with($user->email)->andReturn($user);
        $this->request
            ->shouldReceive('only')
            ->with('email', 'password')
            ->andReturn(['email' => $email, 'password' => $password]);

        $result = $this->authService->resolveLoginUser();

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($result->id, $user->id);
    }

    /** @test */
    public function it_will_return_user_model_when_incoming_login_request_from_spa_is_success(): void
    {
        $email = $this->faker->email;
        $password = $this->faker->password;
        $user = User::factory()->create(['email' => $email]);
        Auth::shouldReceive('attempt')->with(['email' => $email, 'password' => $password])->andReturnTrue();
        $mockSession = $this->spy(SessionManager::class);
        $this->request->shouldReceive('hasHeader')->with('device-name')->andReturnFalse();
        $this->request->shouldReceive('user')->andReturn($user);
        $this->request->shouldReceive('session')->andReturn($mockSession);
        $this->request
            ->shouldReceive('only')
            ->with('email', 'password')
            ->andReturn(['email' => $email, 'password' => $password]);

        $result = $this->authService->resolveLoginUser();

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($result->id, $user->id);
        $mockSession->shouldHaveReceived('regenerate')->once();
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
