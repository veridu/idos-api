<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Extension;

use App\Event\ServiceQueueEventInterface;
use League\Event\EventInterface;

trait QueueCompanyServiceHandlers {
    /**
     * Service Handler Repository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $serviceRepository;
    /**
     * Handler Repository instance.
     *
     * @var \App\Repository\RepositoryInterface
     */
    private $handlerRepository;
    /**
     * Event Factory instance.
     *
     * @var \App\Factory\Event
     */
    private $eventFactory;
    /**
     * Event Emitter instance.
     *
     * @var \League\Event\Emitter
     */
    private $emitter;
    /**
     * Gearman Client instance.
     *
     * @var \GearmanClient
     */
    private $gearmanClient;

    /**
     * Queues Service Handlers' tasks for the given event and company.
     *
     * @param int                                   $companyId    Company identifier
     * @param \App\Event\ServiceQueueEventInterface $event        Event identifier
     * @param array                                 $mergePayload Payload that will be merged into "handler" property
     *
     * @return bool
     */
    public function queueListeningServices(
        int $companyId,
        ServiceQueueEventInterface $event,
        array $mergePayload = []
    ) : bool {
        // find handlers
        $services = $this->serviceRepository->getAllByCompanyIdAndListener($companyId, (string) $event);

        if ($services->isEmpty()) {
            $this->dispatchUnhandleEvent($event);

            return false;
        }

        $success = true;
        foreach ($services as $service) {
            $handlerService = $service->handler_service();
            $handler        = $this->handlerRepository->find($handlerService->handlerId);

            // create payload
            $payload = [
                'name'    => $handlerService->name,
                'user'    => $handler->authUsername,
                'pass'    => $handler->authPassword,
                'url'     => $handlerService->url,
                'handler' => $event->getServiceHandlerPayload($mergePayload)
            ];

            if ($this->queue($payload)) {
                $this->emitter->emit($this->eventFactory->create('Manager\WorkQueued', $event));

                continue;
            }

            $success = false;
            $this->dispatchUnhandleEvent($event);
        }

        return $success;
    }

    /**
     * Queue work on the "manager" work queue.
     *
     * @param string $payload Payload to be sent
     *
     * @return bool
     */
    private function queue(array $payload) : bool {
        $this->gearmanClient->doBackground(
            'manager',
            json_encode($payload),
            uniqid('manager-')
        );

        return $this->gearmanClient->returnCode() === \GEARMAN_SUCCESS;
    }

    /**
     * Dispatches an unhandled event.
     *
     * @param \League\Event\EventInterface $event
     *
     * @return void
     */
    private function dispatchUnhandleEvent(EventInterface $event) {
        $unhandledEvent = $this->eventFactory->create(
            'Manager\UnhandledEvent',
            $event
        );
        $this->emitter->emit($unhandledEvent);
    }
}
