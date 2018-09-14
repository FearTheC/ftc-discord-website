<?php

declare(strict_types=1);

namespace App\Session\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\RedirectResponse;
use GuzzleHttp\Client;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWS;
use App\Session\SessionMiddleware;
use Zend\Diactoros\Response\JsonResponse;

class LoginCallbackHandler implements MiddlewareInterface
{
    
    /**
     * @var JWSLoader
     */
   private $jwsLoader;
   
   /**
    * @var JWK $keys
    */
   private $keys;
    
    /**
     * @var Client
     */
    private $httpClient;
    
    public function __construct(
        Client $httpClient,
        JWSLoader $jwsLoader,
        JWK $keys
        ) {
        $this->httpClient = $httpClient;
        $this->jwsLoader = $jwsLoader;
        $this->keys = $keys;
    }
    
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $params = $request->getQueryParams();
        $redirectUri = $params['redirect_uri'];

        $token = (string) $params['token'];

        $res = $this->jwsLoader->loadAndVerifyWithKey($token, $this->keys, $signature);
        $decodedPayload = json_decode($res->getPayload());
        $data["jws"] = json_decode($res->getPayload());
        $data["token"] = $token;
        
        $this->setCookie($token, $decodedPayload);

            return new RedirectResponse($redirectUri);
    }
    
    private function setCookie(string $token, $payload)
    {
        setcookie(
            \App\Session\SessionMiddleware::DEFAULT_COOKIE,
            $token,
            $payload->exp
        );
    }
    
}
