<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Listener\Company;

use App\Event\Review;
use App\Listener;
use Interop\Container\ContainerInterface;

class ReviewProvider extends Listener\AbstractListenerProvider {
    public function __construct(ContainerInterface $container) {
        $this->events = [
            Review\Created::class => [
                new Listener\LogFiredEventListener($container->get('log')('Event'))
            ],
            Review\Updated::class => [
                new Listener\LogFiredEventListener($container->get('log')('Event'))
            ]
        ];
    }
}
