<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Event\Profile\Attribute;

use App\Entity\Company\Credential;
use App\Entity\Profile\Attribute;
use App\Event\AbstractEvent;
use App\Event\Interfaces\UserIdGetterInterface;

/**
 * Updated event.
 */
class Updated extends AbstractEvent implements UserIdGetterInterface {
    /**
     * Event related Attribute.
     *
     * @var \App\Entity\Profile\Attribute
     */
    public $attribute;
    /**
     * Event related Credential.
     *
     * @var \App\Entity\Company\Credential
     */
    public $credential;

    /**
     * Class constructor.
     *
     * @param \App\Entity\Profile\Attribute  $attribute
     * @param \App\Entity\Company\Credential $credential
     *
     * @return void
     */
    public function __construct(Attribute $attribute, Credential $credential) {
        $this->attribute  = $attribute;
        $this->credential = $credential;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId() : int {
        return $this->attribute->userId;
    }
}
