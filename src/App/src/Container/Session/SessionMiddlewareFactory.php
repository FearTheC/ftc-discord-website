<?php

declare(strict_types=1);

namespace App\Container\Session;

use Psr\Container\ContainerInterface;
use Jose\Component\Core\JWK;
use App\Session\JWSLoader;
use App\Session\SessionMiddleware;

class SessionMiddlewareFactory
{
    
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')->offsetGet('session');
        
        $jwsLoader = $container->get(JWSLoader::class);
        $keys = JWK::create($config['key']);
        
        return new SessionMiddleware($jwsLoader, $keys);
    }
    
}
