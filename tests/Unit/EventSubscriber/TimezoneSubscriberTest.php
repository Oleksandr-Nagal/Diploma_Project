<?php

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\TimezoneSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Extension\CoreExtension;
use Twig\Loader\ArrayLoader;

class TimezoneSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = TimezoneSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
    }

    public function testSetsTimezoneFromCookie(): void
    {
        $twig = new Environment(new ArrayLoader([]));
        $subscriber = new TimezoneSubscriber($twig);

        $request = Request::create('/');
        $request->cookies->set('user_timezone', 'Europe/Kyiv');

        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $subscriber->onKernelRequest($event);

        $core = $twig->getExtension(CoreExtension::class);
        $this->assertSame('Europe/Kyiv', $core->getTimezone()->getName());
    }

    public function testIgnoresInvalidTimezone(): void
    {
        $twig = new Environment(new ArrayLoader([]));
        $subscriber = new TimezoneSubscriber($twig);

        $defaultTz = $twig->getExtension(CoreExtension::class)->getTimezone()->getName();

        $request = Request::create('/');
        $request->cookies->set('user_timezone', 'Invalid/Timezone');

        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $subscriber->onKernelRequest($event);

        $this->assertSame($defaultTz, $twig->getExtension(CoreExtension::class)->getTimezone()->getName());
    }

    public function testIgnoresMissingCookie(): void
    {
        $twig = new Environment(new ArrayLoader([]));
        $subscriber = new TimezoneSubscriber($twig);

        $defaultTz = $twig->getExtension(CoreExtension::class)->getTimezone()->getName();

        $request = Request::create('/');

        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $subscriber->onKernelRequest($event);

        $this->assertSame($defaultTz, $twig->getExtension(CoreExtension::class)->getTimezone()->getName());
    }
}
