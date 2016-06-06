<?php
/**
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace App\Command;

/**
 * Abstract Command Implementation.
 */
abstract class AbstractCommand implements CommandInterface {
    /**
     * {@inheritDoc}
     */
    public function setParameter($name, $value) {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;

            return $this;
        }

        throw new \RuntimeException(sprintf('Invalid property name "%s"', $name));
    }
}
