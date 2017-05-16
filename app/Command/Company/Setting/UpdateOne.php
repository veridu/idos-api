<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Company\Setting;

use App\Command\AbstractCommand;
use App\Command\CommandInterface;

/**
 * Setting "Update One" Command.
 */
class UpdateOne extends AbstractCommand {
    /**
     * Setting's id.
     *
     * @var object
     */
    public $settingId;
    /**
     * Setting's property value (user input).
     *
     * @var object
     */
    public $value;
    /**
     * Target company.
     *
     * @var \App\Entity\Company
     */
    public $company;
    /**
     * Identity.
     *
     * @var \App\Entity\Identity
     */
    public $identity;

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters) : CommandInterface {
        if (isset($parameters['value'])) {
            $this->value = $parameters['value'];
        }

        return $this;
    }
}
