<?php

/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Route;

use App\Middleware\Auth;
use App\Middleware\Permission;
use Interop\Container\ContainerInterface;
use Slim\App;

/**
 * Companies routing definitions.
 *
 * @link docs/companies/overview.md
 * @see App\Controller\Companies
 */
class Companies implements RouteInterface {
    /**
     * {@inheritdoc}
     */
    public static function getPublicNames() : array {
        return [
            'companies:listAll',
            'companies:createNew',
            'companies:deleteAll',
            'companies:getOne',
            'companies:updateOne',
            'companies:deleteOne'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function register(App $app) {
        $app->getContainer()[\App\Controller\Companies::class] = function (ContainerInterface $container) {
            return new \App\Controller\Companies(
                $container->get('repositoryFactory')->create('Company'),
                $container->get('commandBus'),
                $container->get('commandFactory'),
                $container->get('optimus')
            );
        };

        $container            = $app->getContainer();
        $authMiddleware       = $container->get('authMiddleware');
        $permissionMiddleware = $container->get('permissionMiddleware');

        self::listAll($app, $authMiddleware, $permissionMiddleware);
        self::createNew($app, $authMiddleware, $permissionMiddleware);
        self::deleteAll($app, $authMiddleware, $permissionMiddleware);
        self::getOne($app, $authMiddleware, $permissionMiddleware);
        self::updateOne($app, $authMiddleware, $permissionMiddleware);
        self::deleteOne($app, $authMiddleware, $permissionMiddleware);
    }

    /**
     * List all Companies.
     *
     * Retrieve a complete list of all child companies that belong to the requesting company.
     *
     * @apiEndpoint GET /companies
     * @apiGroup Company
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/companies/listAll.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\Companies::listAll
     */
    private static function listAll(App $app, callable $auth, callable $permission) {
        $app
            ->get(
                '/companies',
                'App\Controller\Companies:listAll'
            )
            ->add($permission(Permission::PRIVATE_ACTION))
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('companies:listAll');
    }

    /**
     * Create new Company.
     *
     * Create a new child company for the requesting company.
     *
     * @apiEndpoint POST /companies
     * @apiGroup Company
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/companies/createNew.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\Companies::createNew
     */
    private static function createNew(App $app, callable $auth, callable $permission) {
        $app
            ->post(
                '/companies',
                'App\Controller\Companies:createNew'
            )
            ->add($permission(Permission::PRIVATE_ACTION))
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('companies:createNew');
    }

    /**
     * Delete all Companies.
     *
     * Delete all child companies that belong to the requesting company.
     *
     * @apiEndpoint DELETE /companies
     * @apiGroup Company
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/companies/deleteAll.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\Companies::deleteAll
     */
    private static function deleteAll(App $app, callable $auth) {
        $app
            ->delete(
                '/companies',
                'App\Controller\Companies:deleteAll'
            )
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('companies:deleteAll');
    }

    /**
     * Retrieve a single Company.
     *
     * Retrieves all public information from a Company.
     *
     * @apiEndpoint GET /companies/{companySlug}
     * @apiGroup Company
     * @apiEndpointURIFragment string companySlug veridu-ltd
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/companies/getOne.md
     * @see App\Controller\Companies::getOne
     */
    private static function getOne(App $app, callable $auth, callable $permission) {
        $app
            ->get(
                '/companies/{companySlug:[a-zA-Z0-9_-]+}',
                'App\Controller\Companies:getOne'
            )
            ->add($permission(Permission::PUBLIC_ACTION))
            ->add($auth(Auth::NONE))
            ->setName('companies:getOne');
    }

    /**
     * Update a single Company.
     *
     * Updates Company's specific information.
     *
     * @apiEndpoint PUT /companies/{companySlug}
     * @apiGroup Company
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiEndpointURIFragment string companySlug veridu-ltd
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/companies/updateOne.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\Companies::updateOne
     */
    private static function updateOne(App $app, callable $auth, callable $permission) {
        $app
            ->put(
                '/companies/{companySlug:[a-zA-Z0-9_-]+}',
                'App\Controller\Companies:updateOne'
            )
            ->add($permission(Permission::PRIVATE_ACTION))
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('companies:updateOne');
    }

    /**
     * Deletes a single Company.
     *
     * Deletes the requesting company or a child company that belongs to it.
     *
     * @apiEndpoint DELETE /companies/{companySlug}
     * @apiGroup Company
     * @apiAuth header key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiAuth query key compPrivKey 2f476be4f457ef606f3b9177b5bf19c9 Company's Private Key
     * @apiEndpointURIFragment string companySlug veridu-ltd
     *
     * @param \Slim\App $app
     * @param \callable $auth
     *
     * @return void
     *
     * @link docs/companies/deleteOne.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\Companies::deleteOne
     */
    private static function deleteOne(App $app, callable $auth, callable $permission) {
        $app
            ->delete(
                '/companies/{companySlug:[a-zA-Z0-9_-]+}',
                'App\Controller\Companies:deleteOne'
            )
            ->add($permission(Permission::PRIVATE_ACTION))
            ->add($auth(Auth::COMP_PRIVKEY))
            ->setName('companies:deleteOne');
    }
}
