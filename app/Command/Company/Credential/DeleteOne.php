<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Company\Credential;

use App\Command\AbstractCommand;
use App\Command\CommandInterface;

/**
 * Credential "Delete One" Command.
 */
class DeleteOne extends AbstractCommand {
    /**
     * Credential to be deleted.
     *
     * @var \App\Entity\Company\Credential
     */
    public $credential;

    /**
     * Acting identity.
     *
     * @var \App\Entity\Identity
     */
    public $identity;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) : CommandInterface {
        return $this;
    }
}
