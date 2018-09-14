<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use GuzzleHttp\Psr7\Stream;

class ContainerIdentifierMiddleware implements MiddlewareInterface
{
   
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $response = $handler->handle($request);
        if ($response->getBody()->getSize() && $response->getHeader('Content-Type') == 'text/html') {
            $response = $this->updateCssHrefs($response);
        }
        return $response->withHeader('Instance', getenv('HOSTNAME'));
    }
    
    
    private function updateCssHrefs(ResponseInterface $response)
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getBody()->getContents());
        libxml_use_internal_errors(false);
        
        $elements = $dom->getElementsByTagName( 'link' );

        $i = $elements->count();
        for ($pos = 0; $pos < $i; $pos++) {
            $this->changeCssHrefToCdn($elements->item($pos));
        }
        
        $stream = new \Zend\Diactoros\Stream('php://temp', 'w+b');
        $stream->write($dom->saveHTML());

        return $response->withBody($stream);
    }
    
    
    private function changeCssHrefToCdn(\DOMElement $element)
    {
        if ($element->getAttribute('rel') == 'stylesheet' && substr($element->getAttribute('href'), 0, 13) == '/stylesheets/') {
            $fileHash = md5(file_get_contents('./public'.$element->getAttribute('href')));
            $element->setAttribute('href', 'https://cdn.fearthec.io/stylesheets/'.$fileHash.'.css');
        }
    }
    
}
