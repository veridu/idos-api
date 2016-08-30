<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Factory\Command;
use App\Repository\RoleAccessInterface;
use Jenssegers\Optimus\Optimus;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles requests to /access/roles.
 */
class RoleAccess implements ControllerInterface {
    /**
     * RoleAccess Repository instance.
     *
     * @var App\Repository\RoleAccessInterface
     */
    private $repository;
    /**
     * Command Bus instance.
     *
     * @var \League\Tactician\CommandBus
     */
    private $commandBus;
    /**
     * Command Factory instance.
     *
     * @var App\Factory\Command
     */
    private $commandFactory;

    /**
     * Class constructor.
     *
     * @param App\Repository\RoleAccessInterface $repository
     * @param \League\Tactician\CommandBus       $commandBus
     * @param App\Factory\Command                $commandFactory
     *
     * @return void
     */
    public function __construct(
        RoleAccessInterface $repository,
        CommandBus $commandBus,
        Command $commandFactory,
        Optimus $optimus
    ) {
        $this->repository     = $repository;
        $this->commandBus     = $commandBus;
        $this->commandFactory = $commandFactory;
        $this->optimus        = $optimus;
    }

    /**
     * List all child RoleAccess that belongs to the acting User.
     *
     * @apiEndpointParam query              int page 10|1           Current page.
     *
     * @apiEndpointResponse 200 schema/access/roles/listAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $actingUser = $request->getAttribute('actingUser');
        $entities   = $this->repository->findByIdentity($actingUser->identity_id);

        $body = [
            'data'    => $entities->toArray(),
            'updated' => (
                $entities->isEmpty() ? time() : max($entities->max('updatedAt'), $entities->max('createdAt'))
            )
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Retrieves role access defined to certain role and resource for the acting User.
     *
     * @apiEndpointURIFragment     string roleName         The role name.
     * @apiEndpointURIFragment     string resource         The resource.
     *
     * @apiEndpointResponse 200 schema/access/roles/getOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @throws App\Exception\NotFound
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $actingUser          = $request->getAttribute('actingUser');
        $decodedRoleAccessId = $request->getAttribute('decodedRoleAccessId');

        $entity = $this->repository->findOne($actingUser->identityId, $decodedRoleAccessId);

        $body = [
            'data' => $entity->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Creates a new RoleAccess for the acting User.
     *
     * @apiEndpointRequiredParam body       string role             The role.
     * @apiEndpointRequiredParam body       string resource         The resource.
     * @apiEndpointRequiredParam body       int access              The access.
     *
     * @apiEndpointResponse 201 schema/access/roles/createNew.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createNew(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $actingUser = $request->getAttribute('actingUser');
        $body       = $request->getParsedBody();

        $command = $this->commandFactory->create('RoleAccess\\CreateNew');
        $command
            ->setParameters($request->getParsedBody())
            ->setParameter('identityId', $actingUser->identityId);
        $entity = $this->commandBus->handle($command);

        $body = [
            'data' => $entity->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body)
            ->setParameter('statusCode', 201);

        return $this->commandBus->handle($command);

    }

    /**
     * Deletes all RoleAccess registers that belongs to the acting User.
     *
     * @apiEndpointResponse 200 schema/access/roles/deleteAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $actingUser = $request->getAttribute('actingUser');

        $command = $this->commandFactory->create('RoleAccess\\DeleteAll');
        $command->setParameter('identityId', $actingUser->identityId);

        $deleted = $this->commandBus->handle($command);

        $body = [
            'deleted' => $deleted
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Deletes a RoleAccess of the acting User.
     *
     * @apiEndpointURIFragment     string roleName         The role name.
     * @apiEndpointURIFragment     string resource         The resource.
     *
     * @apiEndpointResponse 200 schema/access/roles/deleteOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @throws App\Exception\NotFound
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $actingUser          = $request->getAttribute('actingUser');
        $decodedRoleAccessId = $request->getAttribute('decodedRoleAccessId');

        $command = $this->commandFactory->create('RoleAccess\\DeleteOne');
        $command->setParameter('identityId', $actingUser->identityId);
        $command->setParameter('roleAccessId', $decodedRoleAccessId);

        $deleted = $this->commandBus->handle($command);

        $body = [
            'status' => (bool) $deleted
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Updates the Target RoleAccess, a child of the Acting RoleAccess.
     *
     * @apiEndpointURIFragment     string roleName         The role name.
     * @apiEndpointURIFragment     string resource         The resource.
     * @apiEndpointRequiredParam body       int access              The access value.
     *
     * @apiEndpointResponse 200 schema/access/roles/updateOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @throws App\Exception\NotFound
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @see App\Command\RoleAccess\UpdateOne
     */
    public function updateOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $actingUser          = $request->getAttribute('actingUser');
        $decodedRoleAccessId = $request->getAttribute('decodedRoleAccessId');

        $command = $this->commandFactory->create('RoleAccess\\UpdateOne');
        $command
            ->setParameters($request->getParsedBody())
            ->setParameter('roleAccessId', $decodedRoleAccessId)
            ->setParameter('identityId', $actingUser->identityId);

        $entity = $this->commandBus->handle($command);

        $body = [
            'data' => $entity->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);

    }
}
