<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Template\TemplateRendererInterface;
use FTC\Discord\Model\Aggregate\Guild;
use App\Session\SessionMiddleware;

class AuthenticationMiddleware implements MiddlewareInterface
{
     /**
      * @var TemplateRendererInterface
      */
    private $template;
    
    /**
     * @var string
     */
    private $oauthServerUri;
    
    
    public function __construct(TemplateRendererInterface $template, $oauthServerUri)
    {
            $this->template = $template;
            $this->oauthServerUri = $oauthServerUri;
    }

    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $guild = $request->getAttribute(Guild::class);

        if ($guild && $session->isEmpty()) {
            $session->set('user', [
                'roles' => [['role_id' => $request->getAttribute(Guild::class)->getId()->__toString()]],
            ]);
        }

        $this->template->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'oauthServerUri', $this->oauthServerUri);
        $this->template->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'user', $session->get('user'));
        
        return $handler->handle($request);
    }
    
}
