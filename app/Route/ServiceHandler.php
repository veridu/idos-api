<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Route;

use App\Middleware\Auth;
use App\Middleware\EndpointPermission;
use Interop\Container\ContainerInterface;
use Slim\App;

/**
 * ServiceHandler routing definitions.
 *
 * @link docs/service-handlers/overview.md
 * @see App\Controller\ServiceHandlers
 */
class ServiceHandler implements RouteInterface {
    /**
     * {@inheritdoc}
     */
    public static function getPublicNames() : array {
        return [
            'service-handlers:listAll',
            'service-handlers:deleteAll',
            'service-handlers:createNew',
            'service-handlers:getOne',
            'service-handlers:updateOne',
            'service-handlers:deleteOne'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function register(App $app) {
        $app->getContainer()[\App\Controller\ServiceHandlers::class] = function (ContainerInterface $container) {
            return new \App\Controller\ServiceHandlers(
                $container->get('repositoryFactory')->create('ServiceHandler'),
                $container->get('commandBus'),
                $container->get('commandFactory'),
                $container->get('optimus')
            );
        };

        $container            = $app->getContainer();
        $authMiddleware       = $container->get('authMiddleware');
        $permissionMiddleware = $container->get('endpointPermissionMiddleware');

        self::listAll($app, $authMiddleware, $permissionMiddleware);
        self::getOne($app, $authMiddleware, $permissionMiddleware);
        self::createNew($app, $authMiddleware, $permissionMiddleware);
        self::deleteOne($app, $authMiddleware, $permissionMiddleware);
        self::deleteAll($app, $authMiddleware, $permissionMiddleware);
        self::updateOne($app, $authMiddleware, $permissionMiddleware);
    }

    /**
     * List all Service handlers.
     *
     * Retrieve a complete list of service handlers that belong to the requesting company.
     *
     * @apiEndpoint GET /service-handlers
     * @apiGroup Company ServiceHandler
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/service-handlers/listAll.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\ServiceHandlers::listAll
     */
    private static function listAll(App $app, callable $auth, callable $permission) {
        $app
            ->get(
                '/service-handlers',
                'App\Controller\ServiceHandlers:listAll'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('service-handlers:listAll');
    }

    /**
     * Create new ServiceHandler.
     *
     * Create a new service handler for the requesting company.
     *
     * @apiEndpoint POST /service-handlers
     * @apiGroup Company ServiceHandler
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/service-handlers/createNew.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\ServiceHandlers::createNew
     */
    private static function createNew(App $app, callable $auth, callable $permission) {
        $app
            ->post(
                '/service-handlers',
                'App\Controller\ServiceHandlers:createNew'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('service-handlers:createNew');
    }

    /**
     * Deletes all service-handlers.
     *
     * Deletes all service handlers that belongs to the requesting company.
     *
     * @apiEndpoint DELETE /service-handlers
     * @apiGroup Company ServiceHandler
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/service-handlers/deleteAll.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\ServiceHandlers::deleteAll
     */
    private static function deleteAll(App $app, callable $auth, callable $permission) {
        $app
            ->delete(
                '/service-handlers',
                'App\Controller\ServiceHandlers:deleteAll'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('service-handlers:deleteAll');
    }

    /**
     * Retrieve a single Service handler.
     *
     * Retrieves all public information from a Service handler.
     *
     * @apiEndpoint GET /service-handlers/{serviceHandlerId}
     * @apiGroup Company ServiceHandler
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiEndpointURIFragment string serviceHandlerId 1
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/service-handlers/getOne.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\ServiceHandlers::getOne
     */
    private static function getOne(App $app, callable $auth, callable $permission) {
        $app
            ->get(
                '/service-handlers/{serviceHandlerId:[0-9]+}',
                'App\Controller\ServiceHandlers:getOne'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('service-handlers:getOne');
    }

    /**
     * Update a single ServiceHandler.
     *
     * Updates a single Service handler instance.
     *
     * @apiEndpoint GET /service-handlers/{serviceHandlerId}
     * @apiGroup Company ServiceHandler
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiEndpointURIFragment  string  serviceHandlerId 1
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/service-handlers/updateOne.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\ServiceHandlers::updateOne
     */
    private static function updateOne(App $app, callable $auth, callable $permission) {
        $app
            ->put(
                '/service-handlers/{serviceHandlerId:[0-9]+}',
                'App\Controller\ServiceHandlers:updateOne'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('service-handlers:updateOne');
    }

    /**
     * Deletes a single ServiceHandler.
     *
     * Deletes a single Service handler that belongs to the requesting company.
     *
     * @apiEndpoint DELETE /service-handlers/{serviceHandlerId}
     * @apiGroup Company ServiceHandler
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiEndpointURIFragment  string  serviceHandlerId 1
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/service-handlers/deleteOne.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\ServiceHandlers::deleteOne
     */
    private static function deleteOne(App $app, callable $auth, callable $permission) {
        $app
            ->delete(
                '/service-handlers/{serviceHandlerId:[0-9]+}',
                'App\Controller\ServiceHandlers:deleteOne'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('service-handlers:deleteOne');
    }
}
