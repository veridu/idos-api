<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Profile\Task;

use App\Entity\Company\Credential;
use App\Entity\Profile\Task;
use App\Event\AbstractEvent;

/**
 * Updated event.
 */
class Updated extends AbstractEvent {
    /**
     * Event related Task.
     *
     * @var \App\Entity\Profile\Task
     */
    public $task;
    /**
     * Event related Credential.
     *
     * @var \App\Entity\Company\Credential
     */
    public $credential;

    /**
     * Class constructor.
     *
     * @param \App\Entity\Profile\Task       $task
     * @param \App\Entity\Company\Credential $credential
     *
     * @return void
     */
    public function __construct(Task $task, Credential $credential) {
        $this->task       = $task;
        $this->credential = $credential;
    }
}
