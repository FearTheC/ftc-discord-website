<?php

declare(strict_types=1);

namespace App\Container\Middleware;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use App\Middleware\AuthenticationMiddleware;

class AuthenticationMiddlewareFactory
{
    public function __invoke(ContainerInterface $container) : AuthenticationMiddleware
    {
        $template = $container->get(TemplateRendererInterface::class);
        $config = $container->get('config')->offsetGet('session');
        
        return new AuthenticationMiddleware($template, $config['oauth_server_uri']);
    }
}
