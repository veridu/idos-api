<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Handler\Profile;

use App\Command\Profile\Candidate\CreateNew;
use App\Command\Profile\Candidate\DeleteAll;
use App\Entity\Profile\Candidate as CandidateEntity;
use App\Exception\Create;
use App\Exception\NotFound;
use App\Exception\Validate;
use App\Factory\Event;
use App\Handler\HandlerInterface;
use App\Repository\RepositoryInterface;
use App\Validator\Profile\Candidate as CandidateValidator;
use Interop\Container\ContainerInterface;
use League\Event\Emitter;
use Respect\Validation\Exceptions\ValidationException;

/**
 * Handles Candidate commands.
 */
class Candidate implements HandlerInterface {
    /**
     * Candidate Repository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $repository;
    /**
     * Candidate Validator instance.
     *
     * @var \App\Validator\Profile\Candidate
     */
    private $validator;
    /**
     * Event factory instance.
     *
     * @var \App\Factory\Event
     */
    private $eventFactory;
    /**
     * Event emitter instance.
     *
     * @var \League\Event\Emitter
     */
    private $emitter;

    /**
     * {@inheritdoc}
     */
    public static function register(ContainerInterface $container) : void {
        $container[self::class] = function (ContainerInterface $container) : HandlerInterface {
            return new \App\Handler\Profile\Candidate(
                $container
                    ->get('repositoryFactory')
                    ->create('Profile\Candidate'),
                $container
                    ->get('validatorFactory')
                    ->create('Profile\Candidate'),
                $container
                    ->get('eventFactory'),
                $container
                    ->get('eventEmitter')
            );
        };
    }

    /**
     * Class constructor.
     *
     * @param \App\Repository\RepositoryInterface $repository
     * @param \App\Validator\Profile\Candidate    $validator
     * @param \App\Factory\Event                  $eventFactory
     * @param \League\Event\Emitter               $emitter
     *
     * @return void
     */
    public function __construct(
        RepositoryInterface $repository,
        CandidateValidator $validator,
        Event $eventFactory,
        Emitter $emitter
    ) {
        $this->repository   = $repository;
        $this->validator    = $validator;
        $this->eventFactory = $eventFactory;
        $this->emitter      = $emitter;
    }

    /**
     * Creates a new candidate data for the given user.
     *
     * @param \App\Command\Profile\Candidate\CreateNew $command
     *
     * @see \App\Repository\DBCandidate::save
     *
     * @throws \App\Exception\Validate\Profile\CandidateException
     * @throws \App\Exception\Create\Profile\CandidateException
     *
     * @return \App\Entity\Profile\Candidate
     */
    public function handleCreateNew(CreateNew $command) : CandidateEntity {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertHandler($command->handler, 'handler');
            $this->validator->assertLongName($command->attribute, 'attribute');
            $this->validator->assertValue($command->value, 'value');
            $this->validator->assertScore($command->support, 'support');
            $this->validator->assertCredential($command->credential, 'credential');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\CandidateException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        $entity = $this->repository->create(
            [
                'user_id'    => $command->user->id,
                'creator'    => $command->handler->id,
                'attribute'  => $command->attribute,
                'value'      => $command->value,
                'support'    => $command->support,
                'created_at' => time()
            ]
        );

        try {
            $entity = $this->repository->save($entity);
            $entity = $this->repository->hydrateRelations($entity);
            $event  = $this->eventFactory->create(
                'Profile\Candidate\Created',
                $command->user,
                $entity,
                $command->credential
            );
            $this->emitter->emit($event);
        } catch (\Exception $exception) {
            throw new Create\Profile\CandidateException('Error while trying to create an candidate', 500, $exception);
        }

        return $entity;
    }

    /**
     * Deletes all candidate data from a given user.
     *
     * @param \App\Command\Profile\Candidate\DeleteAll $command
     *
     * @see \App\Repository\DBCandidate::getAllByUserIdAndNames
     * @see \App\Repository\DBCandidate::deleteByUserId
     *
     * @return int
     */
    public function handleDeleteAll(DeleteAll $command) : int {
        try {
            $this->validator->assertUser($command->user, 'user');
            $this->validator->assertHandler($command->handler, 'handler');
            $this->validator->assertArray($command->queryParams, 'queryParams');
            $this->validator->assertCredential($command->credential, 'credential');
        } catch (ValidationException $exception) {
            throw new Validate\Profile\CandidateException(
                $exception->getFullMessage(),
                400,
                $exception
            );
        }

        // FIXME replace this with a query that is inside the fuckin' repository
        $entities = $this->repository->findBy(
            [
                'user_id' => $command->user->id,
                'creator' => $command->handler->id
            ],
            $command->queryParams
        );

        $affectedRows = 0;

        try {
            $affectedRows = $this->repository->deleteAllByIdList(
                $entities->pluck('id')->all()
            );

            if ($affectedRows) {
                $event = $this->eventFactory->create(
                    'Profile\Candidate\DeletedMulti',
                    $command->user,
                    $entities,
                    $command->credential
                );
                $this->emitter->emit($event);
            }
        } catch (\Exception $exception) {
            throw new NotFound\Profile\CandidateException('Error while deleting all candidates', 500, $exception);
        }

        return $affectedRows;
    }
}
