<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command\Company\Setting;

use App\Command\AbstractCommand;

/**
 * Setting "Delete One" Command.
 */
class DeleteOne extends AbstractCommand {
    /**
     * Company.
     *
     * @var \App\Entity\Company
     */
    public $company;
    /**
     * Setting Id.
     *
     * @var int
     */
    public $settingId;
    /**
     * Identity.
     *
     * @var \App\Entity\Identity
     */
    public $identity;

    /**
     * {@inheritdoc}
     *
     * @return \App\Command\Company\Setting\DeleteOne
     */
    public function setParameters(array $parameters) : self {

        return $this;
    }
}
