<?php
/**
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace App\Repository;

use App\Factory\Model;

/**
 * Database-based Repository Strategy.
 */
class DBStrategy implements RepositoryStrategyInterface {
    /**
     * Model Factory.
     *
     * @var App\Model\ModelFactory
     */
    private $modelFactory;

    /**
     * Class constructor.
     *
     * @param App\Factory\Model $modelFactory
     *
     * @return void
     */
    public function __construct(Model $modelFactory) {
        $this->modelFactory = $modelFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormattedName($repositoryName) {
        return sprintf('DB%s', ucfirst($repositoryName));
    }

    /**
     * {@inheritDoc}
     */
    public function build($className, $repositoryName) {
        return new $className($this->modelFactory->create($repositoryName));
    }
}
