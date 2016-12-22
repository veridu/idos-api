<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Listener\Profile\Recommendation;

use App\Entity\User;
use App\Extension\DispatchesUnhandledEvents;
use App\Extension\QueuesOnManager;
use App\Factory\Event as EventFactory;
use App\Listener\AbstractListener;
use App\Repository\Company\SettingInterface;
use App\Repository\ServiceInterface;
use App\Repository\UserInterface;
use League\Event\Emitter;
use League\Event\EventInterface;
use Monolog\Logger;

/**
 * This listener is responsible to trigger the evaluation of a recommendation for a user.
 */
class EvaluateRecommendationListener extends AbstractListener {
    use DispatchesUnhandledEvents;
    use QueuesOnManager;

    /**
     * Setting Repository.
     *
     * @var \App\Repository\Company\SettingInterface
     */
    private $settingRepository;
    /**
     * Service handler repository.
     *
     * @var \App\Repository\ServiceInterface
     */
    private $serviceRepository;
    /**
     * User Repository.
     *
     * @var \App\Repository\UserInterface
     */
    private $userRepository;
    /**
     * Event Logger.
     *
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * Event Factory.
     *
     * @var \App\Factory\Event
     */
    private $eventFactory;

    /**
     * Gearman client.
     *
     * @var \GearmanClient
     */
    private $gearmanClient;

    /**
     * Class constructor.
     *
     * @param \Monolog\Logger $logger
     *
     * @return void
     */
    public function __construct(
        SettingInterface $settingRepository,
        ServiceInterface $serviceRepository,
        UserInterface $userRepository,
        Logger $logger,
        EventFactory $eventFactory,
        Emitter $emitter,
        \GearmanClient $gearmanClient
    ) {
        $this->settingRepository        = $settingRepository;
        $this->serviceRepository        = $serviceRepository;
        $this->userRepository           = $userRepository;
        $this->logger                   = $logger;
        $this->eventFactory             = $eventFactory;
        $this->emitter                  = $emitter;
        $this->gearmanClient            = $gearmanClient;
    }

    /**
     * Handles the event.
     *
     * @param \League\Event\EventInterface $event
     *
     * @return void
     */
    public function handle(EventInterface $event) {
        $services = $this->serviceRepository->getAllByCompanyIdAndListener($event->credential->companyId, 'idos.recommendation');

        if ($services->isEmpty()) {
            $this->dispatchUnhandledEvent($event, $this->eventFactory, $this->emitter);

            return false;
        }

        try {
            $user = $this->userRepository->find($event->getUserId());
        } catch (\RuntimeException $e) {
            // Fails silently
            return;
        }

        // tries to get by credential->public
        $settings = $this->settingRepository->findByCompanyIdSectionAndProperties(
            $event->credential->companyId,
            'recommendation',
            [
                sprintf('%s.ruleset', $event->credential->public)
            ]
        );

        if ($settings->isEmpty()) {
            // tries to get by company
            $settings = $this->settingRepository->findByCompanyIdSectionAndProperties(
                $event->credential->companyId,
                'recommendation',
                ['ruleset']
            );
        }

        // fails silently
        if ($settings->isEmpty()) {
            $this->logger->debug('Unhandled recommendation process - no rules defined.');

            return false;
        }

        $this->logger->debug('Recommendation process started.');

        // parse rules
        $rules = json_decode($settings->first()->value, true);

        $success = true;

        foreach ($services as $service) {
            $handlerService = $service->handler_service();

            // create payload
            $payload = [
                'name'    => $handlerService->name,
                'user'    => $handlerService->authUsername,
                'pass'    => $handlerService->authPassword,
                'url'     => $handlerService->url,
                'handler' => [
                    'username'  => $user->username,
                    'publickey' => $event->credential->public,
                    'rules'     => $rules
                ]
            ];

            if ($this->queueOnManager($this->gearmanClient, $payload)) {
                $this->emitter->emit($this->eventFactory->create('Manager\\WorkQueued', $event));
                continue;
            }

            $success = false;
            $this->dispatchUnhandleEvent($event);
        }
    }
}
