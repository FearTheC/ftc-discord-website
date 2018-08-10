<?php

declare(strict_types=1);

namespace FTC\WebAdmin\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Zend\Expressive\Template\TemplateRendererInterface;

class HomePage implements RequestHandlerInterface
{

    /**
     * @var TemplateRendererInterface
     */
    private $template;
    
    public function __construct(
        Template\TemplateRendererInterface $template = null
    ) {
        $this->template = $template;
    }

    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        return new HtmlResponse($this->template->render('admin::home-page', $data));
    }
    
}
