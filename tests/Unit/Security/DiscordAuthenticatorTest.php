<?php

namespace App\Tests\Unit\Security;

use App\Security\DiscordAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DiscordAuthenticatorTest extends TestCase
{
    private RouterInterface $router;
    private DiscordAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);
        $this->authenticator = new DiscordAuthenticator($this->router);
    }

    public function testSupportsReturnsFalse(): void
    {
        $request = Request::create('/oauth/discord/check');

        $this->assertFalse($this->authenticator->supports($request));
    }

    public function testOnAuthenticationSuccessRedirectsToDashboard(): void
    {
        $this->router->method('generate')->with('app_dashboard')->willReturn('/');

        $request = Request::create('/');
        $token = $this->createMock(TokenInterface::class);

        $response = $this->authenticator->onAuthenticationSuccess($request, $token, 'main');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/', $response->getTargetUrl());
    }

    public function testOnAuthenticationFailureRedirectsToLogin(): void
    {
        $this->router->method('generate')->with('app_login')->willReturn('/login');

        $request = Request::create('/');
        $exception = new AuthenticationException('Failed');

        $response = $this->authenticator->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login', $response->getTargetUrl());
    }

    public function testAuthenticateReturnsSelfValidatingPassport(): void
    {
        $request = Request::create('/');

        $passport = $this->authenticator->authenticate($request);

        $this->assertInstanceOf(\Symfony\Component\Security\Http\Authenticator\Passport\Passport::class, $passport);
    }
}
