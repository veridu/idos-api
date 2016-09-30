<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Route\Profile;

use App\Middleware\Auth;
use App\Middleware\EndpointPermission;
use App\Route\RouteInterface;
use Interop\Container\ContainerInterface;
use Slim\App;

/**
 * Profile Reviews
 *
 * A Profile Reviews allows a Company to provide feedback on any Attributes the API has extracted that they feel is inaccurate or incorrect. For example if the API has failed a Profile at an 18+ Gate, and later the User provides evidence proving their age is 18+, the Company should use a Profile Reviews to flag this information as inaccurate in order for Veridu to improve the accuracy of the API.
 *
 * @link docs/profiles/review/overview.md
 * @see App\Controller\Profile\Reviews
 */
class Reviews implements RouteInterface {
    /**
     * {@inheritdoc}
     */
    public static function getPublicNames() : array {
        return [
            'review:listAll',
            'review:createNew',
            'review:getOne',
            'review:updateOne'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function register(App $app) {
        $app->getContainer()[\App\Controller\Profile\Reviews::class] = function (ContainerInterface $container) {
            return new \App\Controller\Profile\Reviews(
                $container->get('repositoryFactory')->create('Profile\Review'),
                $container->get('repositoryFactory')->create('User'),
                $container->get('commandBus'),
                $container->get('commandFactory')
            );
        };

        $container            = $app->getContainer();
        $authMiddleware       = $container->get('authMiddleware');
        $permissionMiddleware = $container->get('endpointPermissionMiddleware');

        self::listAll($app, $authMiddleware, $permissionMiddleware);
        self::createNew($app, $authMiddleware, $permissionMiddleware);
        self::getOne($app, $authMiddleware, $permissionMiddleware);
        self::updateOne($app, $authMiddleware, $permissionMiddleware);
    }

    /**
     * List all reviews.
     *
     * Retrieve all reviews from a given user, matching one or more warnings.
     *
     * @apiEndpoint GET /companies/{companySlug}/profiles/{userId}/reviews
     * @apiGroup Profile Reviews
     * @apiAuth header token IdentityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiAuth query token identityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment int userId 1827452
     *
     * @param \Slim\App $app
     * @param \callable $auth
     * @param \callable $permission
     *
     * @return void
     *
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\Profile\Reviews::listAll
     */
    private static function listAll(App $app, callable $auth, callable $permission) {
        $app
            ->get(
                '/companies/{companySlug:[a-zA-Z0-9_-]+}/profiles/{userId:[0-9]+}/reviews',
                'App\Controller\Profile\Reviews:listAll'
            )
            ->add($permission(EndpointPermission::PUBLIC_ACTION))
            ->add($auth(Auth::IDENTITY))
            ->setName('review:listAll');
    }
    /**
     * Creates a new review.
     *
     * Creates a new review for the given user.
     *
     * @apiEndpoint POST /companies/{companySlug}/profiles/{userId}/reviews
     * @apiGroup Profile Reviews
     * @apiAuth header token IdentityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiAuth query token identityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment int userId 1827452
     *
     * @param \Slim\App $app
     * @param \callable $auth
     * @param \callable $permission
     *
     * @return void
     *
     * @link docs/sources/review/createNew.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\Profile\Reviews::createNew
     */
    private static function createNew(App $app, callable $auth, callable $permission) {
        $app
            ->post(
                '/companies/{companySlug:[a-zA-Z0-9_-]+}/profiles/{userId:[0-9]+}/reviews',
                'App\Controller\Profile\Reviews:createNew'
            )
            ->add($permission(EndpointPermission::PUBLIC_ACTION))
            ->add($auth(Auth::IDENTITY))
            ->setName('review:createNew');
    }

    /**
     * Update a review.
     *
     * Updates a review for the given user and reference.
     *
     * @apiEndpoint PUT /companies/{companySlug}/profiles/{userId}/reviews/{reviewId}
     * @apiGroup Profile Reviews
     * @apiAuth header key credentialToken 2f476be4f457ef606f3b9177b5bf19c9 Company's credential token
     * @apiAuth query key credentialToken 2f476be4f457ef606f3b9177b5bf19c9 Company's credential token
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment int userId 1827452
     * @apiEndpointURIFragment int reviewId 21494
     *
     * @param \Slim\App $app
     * @param \callable $auth
     * @param \callable $permission
     *
     * @return void
     *
     * @link docs/management/members/updateOne.md
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\Profile\Reviews::updateOne
     */
    private static function updateOne(App $app, callable $auth, callable $permission) {
        $app
            ->put(
                '/companies/{companySlug:[a-zA-Z0-9_-]+}/profiles/{userId:[0-9]+}/reviews/{reviewId:[0-9]+}',
                'App\Controller\Profile\Reviews:updateOne'
            )
            ->add($permission(EndpointPermission::PUBLIC_ACTION))
            ->add($auth(Auth::IDENTITY))
            ->setName('review:updateOne');
    }

    /**
     * Retrieves a review.
     *
     * Retrieves a review from the given user.
     *
     * @apiEndpoint GET /companies/{companySlug}/profiles/{userId}/reviews/{reviewId}
     * @apiGroup Profile Reviews
     * @apiAuth header token IdentityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiAuth query token identityToken wqxehuwqwsthwosjbxwwsqwsdi A valid Identity Token
     * @apiEndpointURIFragment string companySlug veridu-ltd
     * @apiEndpointURIFragment int userId 1827452
     * @apiEndpointURIFragment int reviewId 21494
     *
     * @param \Slim\App $app
     * @param \callable $auth
     * @param \callable $permission
     *
     * @return void
     *
     * @see App\Middleware\Auth::__invoke
     * @see App\Middleware\Permission::__invoke
     * @see App\Controller\Profile\Reviews::getOne
     */
    private static function getOne(App $app, callable $auth, callable $permission) {
        $app
            ->get(
                '/companies/{companySlug:[a-zA-Z0-9_-]+}/profiles/{userId:[0-9]+}/reviews/{reviewId:[0-9]+}',
                'App\Controller\Profile\Reviews:getOne'
            )
            ->add($permission(EndpointPermission::PUBLIC_ACTION))
            ->add($auth(Auth::IDENTITY))
            ->setName('review:getOne');
    }
}
