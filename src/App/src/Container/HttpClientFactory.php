<?php

declare(strict_types=1);

namespace App\Container;

use Psr\Container\ContainerInterface;
use GuzzleHttp\Client;

class HttpClientFactory
{
    
    public function __invoke(ContainerInterface $container)
    {
        return new Client([
            'base_uri' => 'http://discord-oauth.fearthec.test',
            'defaults' => [
                'exceptions' => true
            ]
        ]);
    }
}
