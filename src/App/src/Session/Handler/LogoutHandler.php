<?php

declare(strict_types=1);

namespace App\Session\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;
use App\Session\SessionMiddleware;

class LogoutHandler implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
//         $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
//         $session->clear();
        setcookie(SessionMiddleware::DEFAULT_COOKIE, '', time()-3600);
        unset($_COOKIE[SessionMiddleware::DEFAULT_COOKIE]);

        $redirectRoute = $request->getHeaders()['referer'][0] ?? self::REDIRECT_ROUTE;
        
        return new RedirectResponse($redirectRoute);
    }
    
}
