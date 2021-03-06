<?php

declare(strict_types=1);

return [
    'dependencies' => [
        'aliases' => [
        ],
        'invokables' => [
            App\Session\Handler\LogoutHandler::class => App\Session\Handler\LogoutHandler::class,
        ],
        'factories'  => [
            \FTC\Discord\Model\Aggregate\GuildRepository::class =>
                \FTC\Discord\Db\Postgresql\Container\GuildRepository::class,
            \FTC\Discord\Model\Aggregate\GuildWebsitePermissionRepository::class =>
                \FTC\Discord\Db\Postgresql\Container\GuildWebsitePermissionRepository::class,
            \FTC\Discord\Model\Aggregate\UserRepository::class =>
                \FTC\Discord\Db\Postgresql\Container\UserRepository::class,
            \FTC\Discord\Model\Aggregate\GuildRoleRepository::class =>
                \FTC\Discord\Db\Postgresql\Container\GuildRoleRepository::class,
            \FTC\Discord\Model\Aggregate\GuildMemberRepository::class =>
                \FTC\Discord\Db\Postgresql\Container\GuildMemberRepository::class,
            \FTC\Discord\Model\Aggregate\GuildChannelRepository::class =>
                \FTC\Discord\Db\Postgresql\Container\GuildChannelRepository::class,
            \FTC\Discord\Model\Aggregate\GuildMessageRepository::class =>
                \FTC\Discord\Db\Postgresql\Container\GuildMessageRepository::class,
            /**
             * Domain services
             */
            FTC\Discord\Model\Service\GuildCreation::class => FTC\Discord\Container\Model\Service\GuildCreationFactory::class,
            
            App\Middleware\AuthenticationMiddleware::class => App\Container\Middleware\AuthenticationMiddlewareFactory::class,
            App\Middleware\AuthorizationMiddleware::class => App\Container\Middleware\AuthorizationMiddlewareFactory::class,
            App\Session\Handler\LoginHandler::class => App\Container\Session\Handler\LoginHandlerFactory::class,
            App\Session\Handler\LoginCallbackHandler::class => App\Container\Session\Handler\LoginCallbackHandlerFactory::class,
            App\Middleware\GuildSetupMiddleware::class => App\Middleware\GuildSetupMiddlewareFactory::class,
            App\Middleware\CommandDispatcherMiddleware::class => App\Container\Middleware\CommandDispatcherMiddlewareFactory::class,
            
            
            \App\Session\JWSLoader::class => \App\Container\Session\JWSLoaderFactory::class,
            
            'database' => FTC\Database\ClientFactory::class,
            'discord_oauth' => App\Container\OAuthFactory::class,
            'http-client' => App\Container\HttpClientFactory::class,
        ],
    ],
];
