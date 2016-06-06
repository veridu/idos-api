<?php
/**
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace App\Command;

/**
 * Company "Delete All" Command.
 */
class CompanyDeleteAll extends AbstractCommand {
    /**
     * All child companies to this Parent Id will be deleted.
     *
     * @var int
     */
    public $parentId;

    /**
     * {@inheritDoc}
     */
    public function setParameters(array $parameters) {
        if (isset($parameters['parentId']))
            $this->parentId = $parameters['parentId'];

        return $this;
    }
}
