<?php

declare(strict_types=1);

namespace App\Session;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWS;
use PSR7Sessions\Storageless\Session\SessionInterface;
use Dflydev\FigCookies\FigResponseCookies;

class SessionMiddleware implements MiddlewareInterface
{
    
    public const SESSION_ATTRIBUTE = 'session';
    public const SESSION_CLAIM = 'user';
    public const DEFAULT_COOKIE = 'slsession';
    
    /**
     * @var JWSLoader $loader
     */
    private $loader;
    
    /**
     * @var JWK $keys
     */
    private $keys;
    
    
    public function __construct(JWSLoader $loader, JWK $keys)
    {
        $this->loader = $loader;
        $this->keys = $keys;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $delegate) : ResponseInterface
    {
        $token = $this->parseToken($request);
        $sessionContainer = $this->extractSessionContainer($token);

        
        return $delegate->handle($request->withAttribute(self::SESSION_ATTRIBUTE, $sessionContainer));
    }

    
    private function parseToken(ServerRequestInterface $request) : ?JWS
    {
        $cookies    = $request->getCookieParams();
        
        if (!isset($cookies[self::DEFAULT_COOKIE])) {
            return null;
        }
        
        try {
            $jws = $this->loader->loadAndVerifyWithKey($cookies[self::DEFAULT_COOKIE], $this->keys, $signature);
        } catch (\Exception $e) {
                return null;
        }
        
        return $jws;
    }
    
    
    private function appendToken(SessionContainer $sessionContainer, ResponseInterface $response, ?JWS $token)
    {
        $sessionContainerChanged = $sessionContainer->hasChanged();

        if ($sessionContainerChanged && $sessionContainer->isEmpty()) {
            return FigResponseCookies::set($response, $token->getEncodedPayload());
        }
        
        if ($sessionContainerChanged
//             || ($this->shouldTokenBeRefreshed($token) && ! $sessionContainer->isEmpty())
            ) {
            return FigResponseCookies::set($response, $this->getTokenCookie($sessionContainer));
        }
        
        return $response;
    }
    
    /**
     * @throws \OutOfBoundsException
     */
    private function extractSessionContainer(?JWS $token) : SessionContainer
    {
        try {
            if (null === $token || ! $this->loader->getJwsVerifier()->verifyWithKey($token, $this->keys, 0)) {
                return SessionContainer::newEmptySession();
            }
                $sessionClaim = self::SESSION_CLAIM;
                $decodedPayload = json_decode($token->getPayload(), true);
            return SessionContainer::fromDecodedTokenPayload($decodedPayload);
        } catch (\BadMethodCallException $invalidToken) {
            return SessionContainer::newEmptySession();
        }
    }
    
    
    /**
     * @throws \BadMethodCallException
     */
    private function getTokenCookie(SessionInterface $sessionContainer) : SetCookie
    {
        $timestamp = $this->timestamp();
        
        return $this
        ->defaultCookie
        ->withValue(
            (new Builder())
            ->setIssuedAt($timestamp)
            ->setExpiration($timestamp + $this->expirationTime)
            ->set(self::SESSION_CLAIM, $sessionContainer)
            ->sign($this->signer, $this->signatureKey)
            ->getToken()
            )
            ->withExpires($timestamp + $this->expirationTime);
    }
    
}
