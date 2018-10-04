<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use FTC\Discord\Model\Aggregate\Guild;
use Zend\Permissions\Rbac\Rbac;
use FTC\Discord\Model\Aggregate\GuildWebsitePermissionRepository;
use FTC\Discord\Model\ValueObject\Snowflake\RoleId;
use FTC\Discord\Model\Collection\GuildRoleIdCollection;
use FTC\Discord\Model\Collection\GuildWebsitePermissionCollection;
use Zend\Expressive\Router\RouteResult;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;
use FTC\Discord\Model\Aggregate\GuildWebsitePermission;
use FTC\Discord\Model\Aggregate\GuildRoleRepository;
use FTC\Discord\Model\ValueObject\Permission;
use FTC\Discord\Model\ValueObject\Snowflake\GuildId;
use App\Session\SessionMiddleware;

class AuthorizationMiddleware implements MiddlewareInterface
{
    
    
    /**
     * @var Rbac $rbac
     */
    private $rbac;
    
    
    /**
     * @var GuildRoleRepository
     */
    private $rolesRepository;
    
    /**
     * @var GuildWebsitePermissionRepository 
     */
    private $permissionRepository;
    
    
    /**
     * @var TemplateRendererInterface
     */
    private $templateRenderer;
    
    
    public function __construct(
        Rbac $rbac,
        GuildWebsitePermissionRepository $permissionRepository,
        GuildRoleRepository $rolesRepository,
        TemplateRendererInterface $template)
    {
        $this->rbac = $rbac;
        $this->permissionRepository = $permissionRepository;
        $this->rolesRepository = $rolesRepository;
        $this->templateRenderer = $template;
    }
   
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $guild = $request->getAttribute(Guild::class);
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $routeName = $request->getAttribute(RouteResult::class)->getMatchedRouteName();
        $everyoneRole = $request->getAttribute('@everyone');
        
        $permissions = $this->initializeRbacPermissions($guild);

        if (!$permissions->hasForRoute($routeName)) {
            $this->addAdministratorPermission($guild->getId(), $routeName);
        }
        
        $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'rbac', $this->rbac);
        
        
        
        // Most simple : a visitor allowed to view a page on an active website
        if ($this->rbac->isGranted((string) $everyoneRole->getId(), $routeName)
            && $guild->isDomainActive()
            ) {
            return $handler->handle($request);
        }
        
        $user = $session->get('user');
        
        /**
         * We allow :
         * - any user on login/logout pages anyway, so that they may identify ;
         * - when the website's active, on any allowed pages for their roles ;
         * - when the website's inactive, only the owner can browse it.
         */
        if (in_array(substr($routeName, 0, 6), ['login.', 'logout']) OR
            $guild->isDomainActive() && $this->isGranted($user, $routeName) OR
            !$guild->isDomainActive() && $guild->getOwnerId()->get() == $user['id']
         ) {
            $this->templateRenderer->addDefaultParam(TemplateRendererInterface::TEMPLATE_ALL, 'user', $user);
            return $handler->handle($request);
        }

        $previousUrl = $request->getHeaders()['referer'][0];
        return new HtmlResponse($this->templateRenderer->render("error::403", ['previousUrl' => $previousUrl], 403));
    }
    
    
    /**
     * Sets base permissions
     * 
     * @param Guild $guild
     * @return GuildWebsitePermissionCollection
     */
    private function initializeRbacPermissions(Guild $guild) : GuildWebsitePermissionCollection
    {
        $permissions = $this->permissionRepository->getGuildPermissions($guild->getId());
        $this->addRbacRoles($guild->getRoles());
        $this->addRbacPermissions($permissions);
        
        return $permissions;
    }
    
    
    private function isGranted($user, $routeName)
    {
        return (!empty(array_filter($user['roles'], function($role) use ($routeName) {
            return $this->rbac->isGranted((string) $role['role_id'], $routeName);
        })));
    }
    
    
    private function addRbacPermissions(GuildWebsitePermissionCollection $permissions) : void
    {
        array_walk(
            $permissions->getIterator(),
            function($permission) {
                $this->rbac->getRole($permission->getRoleName())->addPermission($permission->getRouteName());
            }
       );
    }
    
    
    private function addRbacRoles(GuildRoleIdCollection $roles) : void
    {
        array_walk(
            $roles->getIterator(),
            function($roleId) { $this->rbac->addRole((string) $roleId); }
        );
    }
    
    /**
     * Records new permission for administrators for unknown route. 
     * 
     * Creates a permission entry for the administrator in the DB when a
     * route, new and never reached, hasn't yet registered yet.
     * 
     * @param GuildId $guildId
     * @param string $routeName
     */
    private function addAdministratorPermission(GuildId $guildId, string $routeName)
    {
        $adminRoles = $this->rolesRepository->findByPermission($guildId, new Permission(Permission::ADMINISTRATOR));
        
        array_walk($adminRoles->getIterator(), function ($role) use ($guildId, $routeName) {
            $newPermission = new GuildWebsitePermission($guildId, $role->getId(), $routeName);
            $this->permissionRepository->save($newPermission);
        });
    }
    
}
