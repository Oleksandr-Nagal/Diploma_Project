<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if (in_array($path, ['/favicon.ico', '/robots.txt', '/.well-known/security.txt', '/apple-touch-icon.png', '/null'])) {
            $event->setResponse(new Response('', Response::HTTP_OK));
        }
    }
}
