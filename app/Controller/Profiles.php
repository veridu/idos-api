<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Factory\Command;
use App\Repository\Profile\AttributeInterface;
use App\Repository\Profile\CandidateInterface;
use App\Repository\Profile\GateInterface;
use App\Repository\Profile\ScoreInterface;
use App\Repository\Profile\SourceInterface;
use App\Repository\UserInterface;
use League\Tactician\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles requests to /profiles.
 */
class Profiles implements ControllerInterface {
    /**
     * Profile Repository instance.
     *
     * @var \App\Repository\UserInterface
     */
    private $repository;
    /**
     * Attribute Repository instance.
     *
     * @var \App\Repository\Profile\CandidateInterface
     */
    private $candidateRepository;
    /**
     * Score Repository instance.
     *
     * @var \App\Repository\Profile\ScoreInterface
     */
    private $scoreRepository;
    /**
     * Source Repository instance.
     *
     * @var \App\Repository\Profile\SourceInterface
     */
    private $sourceRepository;
    /**
     * Source Repository instance.
     *
     * @var \App\Repository\Profile\Gate
     */
    private $gateRepository;
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
     * @param \App\Repository\UserInterface              $repository
     * @param \App\Repository\Profile\AttributeInterface $attributeRepository
     * @param \App\Repository\Profile\CandidateInterface $candidateRepository
     * @param \App\Repository\Profile\ScoreInterface     $scoreRepository
     * @param \App\Repository\Profile\SourceInterface    $sourceRepository
     * @param \League\Tactician\CommandBus               $commandBus
     * @param \App\Factory\Command                       $commandFactory
     *
     * @return void
     */
    public function __construct(
        UserInterface $repository,
        AttributeInterface $attributeRepository,
        CandidateInterface $candidateRepository,
        ScoreInterface $scoreRepository,
        SourceInterface $sourceRepository,
        GateInterface $gateRepository,
        CommandBus $commandBus,
        Command $commandFactory
    ) {
        $this->repository          = $repository;
        $this->attributeRepository = $attributeRepository;
        $this->candidateRepository = $candidateRepository;
        $this->scoreRepository     = $scoreRepository;
        $this->sourceRepository    = $sourceRepository;
        $this->gateRepository      = $gateRepository;
        $this->commandBus          = $commandBus;
        $this->commandFactory      = $commandFactory;
    }

    /**
     * Retrieves a single profile.
     *
     * @apiEndpointResponse 200 schema/profile/getOne.json
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @see \App\Repository\CandidateInterface::findByUserId
     * @see \App\Repository\ScoreInterface::getByUserId
     * @see \App\Repository\SourceInterface::getByUserId
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getOne(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface {
        $user = $request->getAttribute('targetUser');

        $attributes = $this->attributeRepository->getAllByUserIdAndNames($user->id);
        $candidates = $this->candidateRepository->findByUserId($user->id);
        $scores     = $this->scoreRepository->getByUserId($user->id);
        $sources    = $this->sourceRepository->getLatest($user->id);
        $gates      = $this->gateRepository->getByUserId($user->id);

        $data = [
            'username'   => $user->username,
            'attributes' => $attributes->toArray(),
            'candidates' => $candidates->toArray(),
            'scores'     => $scores->toArray(),
            'gates'      => $gates->toArray(),
            'sources'    => $sources->toArray(),
            'created_at' => $user->createdAt
        ];

        $body = [
            'data'    => $data,
            'updated' => max(
                $user->updatedAt,
                $attributes->max('updatedAt'),
                $candidates->max('updatedAt'),
                $scores->max('updatedAt'),
                $sources->max('updatedAt')
            )
        ];

        $command = $this->commandFactory->create('ResponseDispatch');
        $command
            ->setParameter('request', $request)
            ->setParameter('response', $response)
            ->setParameter('body', $body);

        return $this->commandBus->handle($command);
    }
}
