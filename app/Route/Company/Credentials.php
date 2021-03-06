<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Route\Company;

use App\Controller\ControllerInterface;
use App\Entity\Role;
use App\Middleware\Auth;
use App\Middleware\EndpointPermission;
use App\Route\RouteInterface;
use Interop\Container\ContainerInterface;
use Slim\App;

/**
 * Company Credentials.
 *
 * Also known as API Keys, a Company Credential is a way of identifying a specific Company accessing the API from a
 * specific origin (eg. using a plugin from website X, or from website Y).
 *
 * @link docs/companies/credentials/overview.md
 * @see \App\Controller\Companies
 */
class Credentials implements RouteInterface {
    /**
     * {@inheritdoc}
     */
    public static function getPublicNames() : array {
        return [
            'credentials:listAll',
            'credentials:createNew',
            'credentials:getOne',
            'credentials:updateOne',
            'credentials:deleteOne'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function register(App $app) : void {
        $app->getContainer()[\App\Controller\Company\Credentials::class] = function (ContainerInterface $container) : ControllerInterface {
            return new \App\Controller\Company\Credentials(
                $container
                    ->get('repositoryFactory')
                    ->create('Company\Credential'),
                $container
                    ->get('commandBus'),
                $container
                    ->get('commandFactory')
            );
        };

        $container            = $app->getContainer();
        $authMiddleware       = $container->get('authMiddleware');
        $permissionMiddleware = $container->get('endpointPermissionMiddleware');

        self::listAll($app, $authMiddleware, $permissionMiddleware);
        self::getOne($app, $authMiddleware, $permissionMiddleware);
        self::createNew($app, $authMiddleware, $permissionMiddleware);
        self::updateOne($app, $authMiddleware, $permissionMiddleware);
        self::deleteOne($app, $authMiddleware, $permissionMiddleware);
    }

    /**
     * List all Credentials.
     *
     * Retrieves a complete list of all credentials that belong to the requesting company.
     *
     * @apiEndpoint GET /companies/{companySlug}/credentials
     * @apiGroup Company
     * @apiAuth header token IdentityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiAuth query token identityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiEndpointURIFragment string companySlug veridu-ltd
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/companies/credentials/listAll.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Company\Credentials::listAll
     */
    private static function listAll(App $app, callable $auth, callable $permission) : void {
        $app
            ->get(
                '/companies/{companySlug:[a-z0-9_-]+}/credentials',
                'App\Controller\Company\Credentials:listAll'
            )
            ->add(
                $permission(
                EndpointPermission::SELF_ACTION | EndpointPermission::PARENT_ACTION,
                Role::COMPANY_OWNER_BIT | Role::COMPANY_ADMIN_BIT | Role::COMPANY_REVIEWER_BIT
                )
            )
            ->add($auth(Auth::IDENTITY))
            ->setName('credentials:listAll');
    }

    /**
     * Create new Credential.
     *
     * Creates a new credential for the requesting company.
     *
     * @apiEndpoint POST /companies/{companySlug}/credentials
     * @apiGroup Company
     * @apiAuth header token IdentityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiAuth query token identityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiEndpointURIFragment string companySlug veridu-ltd
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/companies/credentials/createNew.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Company\Credentials::createNew
     */
    private static function createNew(App $app, callable $auth, callable $permission) : void {
        $app
            ->post(
                '/companies/{companySlug:[a-z0-9_-]+}/credentials',
                'App\Controller\Company\Credentials:createNew'
            )
            ->add(
                $permission(
                EndpointPermission::SELF_ACTION | EndpointPermission::PARENT_ACTION,
                Role::COMPANY_OWNER_BIT | Role::COMPANY_ADMIN_BIT
                )
            )
            ->add($auth(Auth::IDENTITY))
            ->setName('credentials:createNew');
    }

    /**
     * Retrieve a single Credential.
     *
     * Retrieves all public information from a Credential
     *
     * @apiEndpoint GET /companies/{companySlug}/credentials/{pubKey}
     * @apiGroup Company
     * @apiAuth header token IdentityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiAuth query token identityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment string pubKey FEDCBA
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/companies/credentials/getOne.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Company\Credentials::getOne
     */
    private static function getOne(App $app, callable $auth, callable $permission) : void {
        $app
            ->get(
                '/companies/{companySlug:[a-z0-9_-]+}/credentials/{pubKey:[a-zA-Z0-9]+}',
                'App\Controller\Company\Credentials:getOne'
            )
            ->add(
                $permission(
                EndpointPermission::SELF_ACTION | EndpointPermission::PARENT_ACTION,
                Role::COMPANY_OWNER_BIT | Role::COMPANY_ADMIN_BIT
                )
            )
            ->add($auth(Auth::IDENTITY))
            ->setName('credentials:getOne');
    }

    /**
     * Update a single Credential.
     *
     * Updates Credential's specific information
     *
     * @apiEndpoint PATCH /companies/{companySlug}/credentials/{pubKey}
     * @apiGroup Company
     * @apiAuth header token IdentityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiAuth query token identityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment string pubKey FEDCBA
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/companies/credentials/updateOne.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Company\Credentials::updateOne
     */
    private static function updateOne(App $app, callable $auth, callable $permission) : void {
        $app
            ->patch(
                '/companies/{companySlug:[a-z0-9_-]+}/credentials/{pubKey:[a-zA-Z0-9]+}',
                'App\Controller\Company\Credentials:updateOne'
            )
            ->add(
                $permission(
                EndpointPermission::SELF_ACTION | EndpointPermission::PARENT_ACTION,
                Role::COMPANY_OWNER_BIT | Role::COMPANY_ADMIN_BIT
                )
            )
            ->add($auth(Auth::IDENTITY))
            ->setName('credentials:updateOne');
    }

    /**
     * Deletes a single Credential.
     *
     * Deletes a single Credential that belongs to the target company.
     *
     * @apiEndpoint DELETE /companies/{companySlug}/credentials/{pubKey}
     * @apiGroup Company
     * @apiAuth header token IdentityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiAuth query token identityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment string pubKey FEDCBA
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/companies/credentials/deleteOne.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Company\Credentials::deleteOne
     */
    private static function deleteOne(App $app, callable $auth, callable $permission) : void {
        $app
            ->delete(
                '/companies/{companySlug:[a-z0-9_-]+}/credentials/{pubKey:[a-zA-Z0-9]+}',
                'App\Controller\Company\Credentials:deleteOne'
            )
            ->add(
                $permission(
                EndpointPermission::SELF_ACTION | EndpointPermission::PARENT_ACTION,
                Role::COMPANY_OWNER_BIT | Role::COMPANY_ADMIN_BIT
                )
            )
            ->add($auth(Auth::IDENTITY))
            ->setName('credentials:deleteOne');
    }
}
