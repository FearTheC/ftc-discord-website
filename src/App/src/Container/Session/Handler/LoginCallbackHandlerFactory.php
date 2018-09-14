<?php

declare(strict_types=1);

namespace App\Container\Session\Handler;

use Psr\Container\ContainerInterface;
use App\Session\Handler\LoginCallbackHandler;
use App\Session\JWSLoader;
use Jose\Component\Core\JWK;

class LoginCallbackHandlerFactory
{
    public function __invoke(ContainerInterface $container) : LoginCallbackHandler
    {
        $config = $container->get('config')->offsetGet('session');
        
        $httpClient = $container->get('http-client');
        $jwsLoader = $container->get(JWSLoader::class);
        $keys = JWK::create($config['key']);
        
        return new LoginCallbackHandler($httpClient, $jwsLoader, $keys);
    }
}
