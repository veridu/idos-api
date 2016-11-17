<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Profile\Flag;

use App\Entity\Profile\Flag;
use App\Entity\Company\Credential;
use App\Event\AbstractEvent;

/**
 * Created event.
 */
class Created extends AbstractEvent {
    /**
     * Event related Flag.
     *
     * @var \App\Entity\Profile\Flag
     */
    public $flag;
    /**
     * Event related Credential.
     *
     * @var \App\Entity\Company\Credential
     */
    public $credential;

    /**
     * Class constructor.
     *
     * @param \App\Entity\Profile\Flag $flag
     * @param \App\Entity\Company\Credential $credential
     *
     * @return void
     */
    public function __construct(Flag $flag, Credential $credential) {
        $this->flag = $flag;
        $this->credential = $credential;
    }
}
