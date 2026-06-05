<?php

namespace App\Tests\Unit\Security;

use App\Entity\User;
use App\Security\GoogleAuthenticator;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class GoogleAuthenticatorTest extends TestCase
{
    private RouterInterface&MockObject $router;
    private RequestStack $requestStack;
    private EntityManagerInterface&MockObject $em;
    private UserRepository&MockObject $userRepo;
    private ClientRegistry&MockObject $clientRegistry;
    private GoogleAuthenticator $authenticator;

    protected function setUp(): void
    {
        $this->clientRegistry = $this->createMock(ClientRegistry::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->userRepo = $this->createMock(UserRepository::class);
        $this->requestStack = new RequestStack();

        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('/');
        $request->setSession($session);
        $this->requestStack->push($request);

        $this->authenticator = new GoogleAuthenticator(
            $this->clientRegistry,
            $this->em,
            $this->router,
            $this->userRepo,
            $this->requestStack
        );
    }

    public function testSupportsGoogleCheckRoute(): void
    {
        $request = Request::create('/oauth/google/check');
        $request->attributes->set('_route', 'oauth_google_check');

        $this->assertTrue($this->authenticator->supports($request));
    }

    public function testDoesNotSupportOtherRoutes(): void
    {
        $request = Request::create('/login');
        $request->attributes->set('_route', 'app_login');

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

    public function testOnAuthenticationFailureBannedRedirectsToBanned(): void
    {
        $this->router->method('generate')
            ->willReturnMap([
                ['app_banned', [], '/banned'],
                ['app_login', [], '/login'],
            ]);

        $request = Request::create('/');
        $exception = new CustomUserMessageAuthenticationException('Ваш акаунт заблоковано');

        $response = $this->authenticator->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/banned', $response->getTargetUrl());
    }

    public function testOnAuthenticationFailureNonBanMessageGoesToLogin(): void
    {
        $this->router->method('generate')
            ->willReturnMap([
                ['app_banned', [], '/banned'],
                ['app_login', [], '/login'],
            ]);

        $request = Request::create('/');
        $exception = new CustomUserMessageAuthenticationException('Some other error');

        $response = $this->authenticator->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login', $response->getTargetUrl());
    }
}
