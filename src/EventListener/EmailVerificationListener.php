<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class EmailVerificationListener implements EventSubscriberInterface
{
    public function __construct(private RouterInterface $router)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $user = $request->getUser();

        // Routes exclues (ne nécessitant pas de vérification email)
        $excludedRoutes = [
            'app_login',
            'app_logout',
            'app_register',
            'app_verify_email',
        ];

        $currentRoute = $request->attributes->get('_route');

        // Si l'utilisateur est connecté ET que son email n'est pas vérifié
        // ET qu'il n'est pas sur une route exclue
        if ($user && !$user->isVerified() && !in_array($currentRoute, $excludedRoutes)) {
            // Rediriger vers une page "email not verified"
            $verificationPage = $this->router->generate('app_email_not_verified');
            $event->setResponse(new RedirectResponse($verificationPage));
        }
    }
}

