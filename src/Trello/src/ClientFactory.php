<?php declare(strict_types=1);

namespace FTC\Trello;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container) : Client
    {
        $httpClient = new \GuzzleHttp\Client();
        $config = $container->get('config')->offsetGet('trello'); 
        
        return new Client($httpClient, $config);
    }
}
