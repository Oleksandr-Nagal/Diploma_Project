<?php

namespace App\Tests\Unit\EventListener;

use App\Entity\User;
use App\EventListener\BannedUserListener;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class BannedUserListenerTest extends TestCase
{
    private Security $security;
    private RouterInterface $router;
    private BannedUserListener $listener;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->listener = new BannedUserListener($this->security, $this->router);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = BannedUserListener::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
    }

    public function testSkipsSubRequests(): void
    {
        $event = $this->createEvent(false);

        $this->listener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testSkipsBannedRoute(): void
    {
        $event = $this->createEvent(true, 'app_banned');

        $this->listener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testSkipsLogoutRoute(): void
    {
        $event = $this->createEvent(true, 'app_logout');

        $this->listener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testSkipsProfilerPaths(): void
    {
        $event = $this->createEvent(true, 'profiler', '/_profiler/abc');

        $this->listener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testSkipsWhenNoUser(): void
    {
        $this->security->method('getUser')->willReturn(null);
        $event = $this->createEvent(true, 'app_dashboard', '/');

        $this->listener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testSkipsWhenUserNotBanned(): void
    {
        $user = new User();
        $user->setIsBanned(false);

        $this->security->method('getUser')->willReturn($user);
        $event = $this->createEvent(true, 'app_dashboard', '/');

        $this->listener->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testRedirectsWhenUserBanned(): void
    {
        $user = new User();
        $user->setIsBanned(true);
        $user->setBannedUntil(null);

        $this->security->method('getUser')->willReturn($user);
        $this->router->method('generate')->with('app_banned')->willReturn('/banned');

        $event = $this->createEvent(true, 'app_dashboard', '/');

        $this->listener->onKernelRequest($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());
        $this->assertSame('/banned', $event->getResponse()->getTargetUrl());
    }

    private function createEvent(bool $isMainRequest, string $route = '', string $path = '/'): RequestEvent
    {
        $request = Request::create($path);
        $request->attributes->set('_route', $route);

        $kernel = $this->createMock(HttpKernelInterface::class);
        $type = $isMainRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST;

        return new RequestEvent($kernel, $request, $type);
    }
}
