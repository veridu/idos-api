<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Command;

/**
 * Command Interface.
 */
interface CommandInterface {
    /**
     * Sets multiple command parameters.
     *
     * @return \App\Command\CommandInterface
     */
    public function setParameters(array $parameters) : CommandInterface;
    /**
     * Sets a command parameter.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws \RuntimeException
     *
     * @return \App\Command\CommandInterface
     */
    public function setParameter(string $name, $value) : CommandInterface;
}
