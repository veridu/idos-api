<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Handler;

use App\Command\Service\CreateNew;
use App\Command\Service\DeleteAll;
use App\Command\Service\DeleteOne;
use App\Command\Service\UpdateOne;
use App\Entity\Service as ServiceEntity;
use App\Event\Service\Created;
use App\Event\Service\Deleted;
use App\Event\Service\DeletedMulti;
use App\Event\Service\Updated;
use App\Exception\AppException;
use App\Exception\NotAllowed;
use App\Exception\NotFound;
use App\Repository\ServiceInterface;
use App\Validator\Service as ServiceValidator;
use Interop\Container\ContainerInterface;
use League\Event\Emitter;

/**
 * Handles Service commands.
 */
class Service implements HandlerInterface {
    /**
     * Service Repository instance.
     *
     * @var App\Repository\ServiceInterface
     */
    protected $repository;
    /**
     * Service Validator instance.
     *
     * @var App\Validator\Service
     */
    protected $validator;
    /**
     * Event emitter instance.
     *
     * @var League\Event\Emitter
     */
    protected $emitter;

    /**
     * {@inheritdoc}
     */
    public static function register(ContainerInterface $container) {
        $container[self::class] = function (ContainerInterface $container) {
            return new \App\Handler\Service(
                $container
                    ->get('repositoryFactory')
                    ->create('Service'),
                $container
                    ->get('validatorFactory')
                    ->create('Service'),
                $container
                    ->get('eventEmitter')
            );
        };
    }

    /**
     * Class constructor.
     *
     * @param App\Repository\ServiceInterface $repository
     * @param App\Validator\Service           $validator
     *
     * @return void
     */
    public function __construct(
        ServiceInterface $repository,
        ServiceValidator $validator,
        Emitter $emitter
    ) {
        $this->repository = $repository;
        $this->validator  = $validator;
        $this->emitter    = $emitter;
    }

    /**
     * Creates a new Service.
     *
     * @param App\Command\Service\CreateNew $command
     *
     * @return App\Entity\Service
     */
    public function handleCreateNew(CreateNew $command) : ServiceEntity {
        $this->validator->assertCompany($command->company);
        $this->validator->assertName($command->name);
        $this->validator->assertUrl($command->url);
        $this->validator->assertName($command->authUsername);
        $this->validator->assertPassword($command->authPassword);
        $this->validator->assertArray($command->listens);
        $this->validator->assertArray($command->triggers);
        $this->validator->assertAccessMode($command->access);
        $this->validator->assertFlag($command->enabled);

        try {
            $entity = $this->repository->create(
                [
                    'company_id'    => $command->company->id,
                    'name'          => $command->name,
                    'url'           => $command->url,
                    'auth_username' => $command->authUsername,
                    'auth_password' => $command->authPassword,
                    'public'        => sha1('pub' . $command->company->id . microtime()),
                    'private'       => sha1('priv' . $command->company->id . microtime()),
                    'listens'       => $command->listens,
                    'triggers'      => $command->triggers,
                    'access'        => $command->access,
                    'enabled'       => $command->enabled,
                    'created_at'    => time()
                ]
            );
            $entity = $this->repository->save($entity);
            $event  = new Created($entity);
            $this->emitter->emit($event);
        } catch (\Exception $e) {
                throw $e;
            throw new AppException('Error while trying to create a service');
        }

        return $entity;
    }

    /**
     * Updates a Service.
     *
     * @param App\Command\Service\UpdateOne $command
     *
     * @return App\Entity\Service
     */
    public function handleUpdateOne(UpdateOne $command) : ServiceEntity {
        $this->validator->assertCompany($command->company);
        $this->validator->assertId($command->serviceId);

        $input = [];
        if ($command->name) {
            $this->validator->assertName($command->name);
            $input['name'] = $command->name;
        }

        if ($command->listens) {
            $this->validator->assertArray($command->listens);
            $input['listens'] = $command->listens;
        }

        if ($command->triggers) {
            $this->validator->assertTriggers($command->triggers);
            $input['triggers'] = $command->triggers;
        }

        if ($command->url) {
            $this->validator->assertUrl($command->url);
            $input['url'] = $command->url;
        }

        if ($command->access !== null) {
            $this->validator->assertAccess($command->access);
            $input['access'] = $command->access;
        }

        if ($command->enabled !== null) {
            $this->validator->assertFlag($command->enabled);
            $input['enabled'] = $command->enabled;
        }

        if ($command->authUsername) {
            $this->validator->assertAuthUsername($command->authUsername);
            $input['auth_username'] = $command->authUsername;
        }

        if ($command->authPassword) {
            $this->validator->assertAuthPassword($command->authPassword);
            $input['auth_password'] = $command->authPassword;
        }

        $entity = $this->repository->findOne($command->serviceId, $command->company);

        // Any thoughts on a better place of verifying this
        if ($command->company->id != $entity->companyId) {
            throw new NotAllowed();
        }

        $backup = $entity->toArray();

        foreach ($input as $key => $value) {
            $entity->$key = $value;
        }

        if ($backup != $entity->toArray()) {
            try {
                $entity->updatedAt = time();
                $entity            = $this->repository->save($entity);
                $event             = new Updated($entity);
                $this->emitter->emit($event);
            } catch (\Exception $e) {
                throw new AppException('Error while trying to update a service id ' . $command->serviceId);
            }
        }

        return $entity;
    }

    /**
     * Deletes all service handlers ($command->companyId).
     *
     * @param App\Command\Service\DeleteAll $command
     *
     * @return int
     */
    public function handleDeleteAll(DeleteAll $command) : int {
        $this->validator->assertCompany($command->company);

        $services = $this->repository->getAllByCompanyId($command->company->id);

        $affectedRows = $this->repository->deleteByCompanyId($command->company->id);

        $event = new DeletedMulti($services);
        $this->emitter->emit($event);

        return $affectedRows;
    }

    /**
     * Deletes a Service.
     *
     * @param App\Command\Service\DeleteOne $command
     *
     * @throws App\Exception\NotFound
     *
     * @return int
     */
    public function handleDeleteOne(DeleteOne $command) : int {
        $this->validator->assertCompany($command->company);
        $this->validator->assertId($command->serviceId);

        $service = $this->repository->find($command->serviceId);

        $rowsAffected = $this->repository->deleteOne($command->serviceId, $command->company);

        if ($rowsAffected) {
            $event = new Deleted($service);
            $this->emitter->emit($event);
        } else {
            throw new NotFound();
        }

        return $rowsAffected;
    }
}
