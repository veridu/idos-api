<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Factory\Command;
use App\Repository\ServiceInterface;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles requests to /companies/{companySlug}/handlers and /companies/{companySlug}/handlers/{handlerId}.
 */
class Handlers implements ControllerInterface {
    /**
     * ServiceHandler Repository instance.
     *
     * @var \App\Repository\ServiceInterface
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
     * @var \App\Factory\Command
     */
    private $commandFactory;

    /**
     * Class constructor.
     *
     * @param \App\Repository\ServiceInterface $repository
     * @param \League\Tactician\CommandBus            $commandBus
     * @param \App\Factory\Command                    $commandFactory
     *
     * @return void
     */
    public function __construct(
        ServiceInterface $repository,
        CommandBus $commandBus,
        Command $commandFactory
    ) {
        $this->repository     = $repository;
        $this->commandBus     = $commandBus;
        $this->commandFactory = $commandFactory;
    }

    /**
     * Lists all Service handlers that belongs to the acting Company.
     *
     * @apiEndpointResponse 200 schema/handlers/listAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Repository\DBService::getAllByCompanyId
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function listAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company  = $request->getAttribute('targetCompany');
        $entities = $this->repository->getByServiceCompanyId($company->id);

        $body = [
            'data'    => $entities->toArray(),
            'updated' => (
                $entities->isEmpty() ? time() : max($entities->max('createdAt'), $entities->max('updatedAt'))
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
     * Retrieves one Service handler of the acting Company.
     *
     * @apiEndpointResponse 200 schema/handler/getOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Repository\DBService::findOne
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company          = $request->getAttribute('targetCompany');
        $handlerHandlerId = (int) $request->getAttribute('decodedServiceHandlerId');

        $entity = $this->repository->findOne($handlerHandlerId, $company->id);

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
     * Creates a new ServiceHandler for the acting Company.
     *
     * @apiEndpointRequiredParam    body    int     service_id   1325   Service's id.
     * @apiEndpointRequiredParam    body    array   listens     ['source.add.facebook']   Service handler's listens property.
     *
     * @apiEndpointResponse 201 schema/handler/createNew.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\ServiceHandler::handleCreateNew
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function createNew(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company  = $request->getAttribute('targetCompany');
        $identity = $request->getAttribute('identity');

        $command = $this->commandFactory->create('ServiceHandler\\CreateNew');
        $command
            ->setParameters($request->getParsedBody() ?: [])
            ->setParameter('identity', $identity)
            ->setParameter('companyId', $company->id);

        $entity = $this->commandBus->handle($command);

        $body = [
            'status' => true,
            'data'   => $entity->toArray()
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('statusCode', 201)
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Updates one Service handler of the acting Company.
     *
     * @apiEndpointRequiredParam    body    array      listens          Service handler's listens.
     *
     * @apiEndpointResponse 200 schema/handler/updateOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\ServiceHandler::handleUpdateOne
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function updateOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company          = $request->getAttribute('targetCompany');
        $identity         = $request->getAttribute('identity');
        $handlerHandlerId = $request->getAttribute('decodedServiceHandlerId');

        $command = $this->commandFactory->create('ServiceHandler\\UpdateOne');
        $command
            ->setParameters($request->getParsedBody() ?: [])
            ->setParameter('handlerId', $handlerHandlerId)
            ->setParameter('identity', $identity)
            ->setParameter('companyId', $company->id);

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

    /**
     * Deletes one Service handler of the acting Company.
     *
     * @apiEndpointResponse 200 schema/handler/deleteOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\ServiceHandler::handleDeleteOne
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company          = $request->getAttribute('targetCompany');
        $identity         = $request->getAttribute('identity');
        $handlerHandlerId = $request->getAttribute('decodedServiceHandlerId');

        $command = $this->commandFactory->create('ServiceHandler\\DeleteOne');
        $command
            ->setParameter('companyId', $company->id)
            ->setParameter('identity', $identity)
            ->setParameter('handlerId', $handlerHandlerId);

        $this->commandBus->handle($command);
        $body = [
            'status' => true
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }

    /**
     * Deletes all Service handlers that belongs to the acting Company.
     *
     * @apiEndpointResponse 200 schema/handler/deleteAll.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Handler\ServiceHandler::handleDeleteAll
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteAll(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $company  = $request->getAttribute('targetCompany');
        $identity = $request->getAttribute('identity');

        $command = $this->commandFactory->create('ServiceHandler\\DeleteAll');
        $command
            ->setParameter('identity', $identity)
            ->setParameter('companyId', $company->id);

        $body = [
            'deleted' => $this->commandBus->handle($command)
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }
}
