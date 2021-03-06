<?php
/*/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Route\Profile;

use App\Controller\ControllerInterface;
use App\Middleware\Auth;
use App\Middleware\EndpointPermission;
use App\Route\RouteInterface;
use Interop\Container\ContainerInterface;
use Slim\App;

/**
 * Profile Feature.
 *
 * A Profile Feature is a specific question the API can answer using data extracted from the Profile.
 * An example feature could be "quantity of uploaded photos" and the API will give a quantitative answer.
 * Another example feature could be "User's Facebook and Twitter name matches" and the API will give a yes-no answer.
 *
 * @link docs/profile/features/overview.md
 * @see \App\Controller\Profile\Features
 */
class Features implements RouteInterface {
    /**
     * {@inheritdoc}
     */
    public static function getPublicNames() : array {
        return [
            'features:listAll',
            'features:getOne',
            'features:createNew',
            'features:updateOne',
            'features:upsertOne',
            'features:upsertBulk',
            'features:deleteOne',
            'features:deleteAll'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function register(App $app) : void {
        $app->getContainer()[\App\Controller\Profile\Features::class] = function (ContainerInterface $container) : ControllerInterface {
            $repositoryFactory = $container->get('repositoryFactory');

            return new \App\Controller\Profile\Features(
                $repositoryFactory
                    ->create('Profile\Feature'),
                $repositoryFactory
                    ->create('Profile\Source'),
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
        self::upsertOne($app, $authMiddleware, $permissionMiddleware);
        self::upsertBulk($app, $authMiddleware, $permissionMiddleware);
        self::deleteOne($app, $authMiddleware, $permissionMiddleware);
        self::deleteAll($app, $authMiddleware, $permissionMiddleware);
    }

    /**
     * List all Features.
     *
     * Retrieve a complete list of all features that belong to the given user.
     *
     * @apiEndpoint GET /profiles/{userName}/features
     * @apiGroup Profile
     * @apiAuth header token CredentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiAuth query token credentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiEndpointURIFragment string userName 9fd9f63e0d6487537569075da85a0c7f2
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/profile/features/listAll.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Profile\Features::listAll
     */
    private static function listAll(App $app, callable $auth, callable $permission) : void {
        $app
            ->get(
                '/profiles/{userName:[a-zA-Z0-9_-]+}/features',
                'App\Controller\Profile\Features:listAll'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::CREDENTIAL))
            ->setName('features:listAll');
    }

    /**
     * Retrieve a single Feature.
     *
     * Retrieves all public information from a Feature.
     *
     * @apiEndpoint GET /profiles/{userName}/features/{featureId}
     * @apiGroup Profile
     * @apiAuth header token CredentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiAuth query token credentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiEndpointURIFragment string userName 9fd9f63e0d6487537569075da85a0c7f2
     * @apiEndpointURIFragment int featureId 3214
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/profile/features/getOne.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Profile\Features::getOne
     */
    private static function getOne(App $app, callable $auth, callable $permission) : void {
        $app
            ->get(
                '/profiles/{userName:[a-zA-Z0-9_-]+}/features/{featureId:[0-9]+}',
                'App\Controller\Profile\Features:getOne'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::CREDENTIAL))
            ->setName('features:getOne');
    }

    /**
     * Create new Feature.
     *
     * Create a new feature for the given user.
     *
     * @apiEndpoint POST /profiles/{userName}/features
     * @apiGroup Profile
     * @apiAuth header token CredentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiAuth query token credentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiEndpointURIFragment string userName 9fd9f63e0d6487537569075da85a0c7f2
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/profile/features/createNew.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Profile\Features::createNew
     */
    private static function createNew(App $app, callable $auth, callable $permission) : void {
        $app
            ->post(
                '/profiles/{userName:[a-zA-Z0-9_-]+}/features',
                'App\Controller\Profile\Features:createNew'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::CREDENTIAL))
            ->setName('features:createNew');
    }

    /**
     * Update a single Feature.
     *
     * Updates Feature's specific information.
     *
     * @apiEndpoint PATCH /profiles/{userName}/features/{featureId}
     * @apiGroup Profile
     * @apiAuth header token CredentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiAuth query token credentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiEndpointURIFragment string userName 9fd9f63e0d6487537569075da85a0c7f2
     * @apiEndpointURIFragment int featureId 3214
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/profile/features/updateOne.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Profile\Features::updateOne
     */
    private static function updateOne(App $app, callable $auth, callable $permission) : void {
        $app
            ->patch(
                '/profiles/{userName:[a-zA-Z0-9_-]+}/features/{featureId:[0-9]+}',
                'App\Controller\Profile\Features:updateOne'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::CREDENTIAL))
            ->setName('features:updateOne');
    }

    /**
     * Create or update a feature.
     *
     * Create or update a feature for the given user.
     *
     * @apiEndpoint PUT /profiles/{userName}/features
     * @apiGroup Profile
     * @apiAuth header token  CredentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiAuth query token  credentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiEndpointURIFragment string userName 9fd9f63e0d6487537569075da85a0c7f2
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/profile/features/upsert.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Profile\Features::upsert
     */
    private static function upsertOne(App $app, callable $auth, callable $permission) : void {
        $app
            ->put(
                '/profiles/{userName:[a-zA-Z0-9_-]+}/features',
                'App\Controller\Profile\Features:upsertOne'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::CREDENTIAL))
            ->setName('features:upsertOne');
    }

    /**
     * Create or update features.
     *
     * Create or update features for the given user.
     *
     * @apiEndpoint PUT /profiles/{userName}/features/bulk
     * @apiGroup Profile
     * @apiAuth header token  CredentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiAuth query token  credentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiEndpointURIFragment string userName 9fd9f63e0d6487537569075da85a0c7f2
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/profile/features/upsertBulk.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Profile\Features::upsertBulk
     */
    private static function upsertBulk(App $app, callable $auth, callable $permission) : void {
        $app
            ->put(
                '/profiles/{userName:[a-zA-Z0-9_-]+}/features/bulk',
                'App\Controller\Profile\Features:upsertBulk'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::CREDENTIAL))
            ->setName('features:upsertBulk');
    }

    /**
     * Deletes a single Feature.
     *
     * Deletes a single Feature that belongs to the given user
     *
     * @apiEndpoint DELETE /profiles/{userName}/features/{featureId}
     * @apiGroup Profile
     * @apiAuth header token CredentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiAuth query token credentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiEndpointURIFragment string userName 9fd9f63e0d6487537569075da85a0c7f2
     * @apiEndpointURIFragment int featureId 3214
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/profile/features/deleteOne.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Profile\Features::deleteOne
     */
    private static function deleteOne(App $app, callable $auth, callable $permission) : void {
        $app
            ->delete(
                '/profiles/{userName:[a-zA-Z0-9_-]+}/features/{featureId:[0-9]+}',
                'App\Controller\Profile\Features:deleteOne'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::CREDENTIAL))
            ->setName('features:deleteOne');
    }

    /**
     * Deletes all features.
     *
     * Deletes all features that belongs to the given user
     *
     * @apiEndpoint DELETE /profiles/{userName}/features
     * @apiGroup Profile
     * @apiAuth header token CredentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiAuth query token credentialToken wqxehuwqwsthwosjbxwwsqwsdi A valid Credential Token
     * @apiEndpointURIFragment string userName 9fd9f63e0d6487537569075da85a0c7f2
     *
     * @param \Slim\App $app
     * @param callable  $auth
     * @param callable  $permission
     *
     * @return void
     *
     * @link docs/profile/features/deleteAll.md
     * @see \App\Middleware\Auth::__invoke
     * @see \App\Middleware\Permission::__invoke
     * @see \App\Controller\Profile\Features::deleteAll
     */
    private static function deleteAll(App $app, callable $auth, callable $permission) : void {
        $app
            ->delete(
                '/profiles/{userName:[a-zA-Z0-9_-]+}/features',
                'App\Controller\Profile\Features:deleteAll'
            )
            ->add($permission(EndpointPermission::PRIVATE_ACTION))
            ->add($auth(Auth::CREDENTIAL))
            ->setName('features:deleteAll');
    }
}
