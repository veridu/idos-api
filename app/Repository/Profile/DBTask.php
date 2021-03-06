<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository\Profile;

use App\Repository\AbstractDBRepository;

/**
 * Database-based Task Repository Implementation.
 */
class DBTask extends AbstractDBRepository implements TaskInterface {
    /**
     * The table associated with the repository.
     *
     * @var string
     */
    protected $tableName = 'tasks';

    /**
     * The entity associated with the repository.
     *
     * @var string
     */
    protected $entityName = 'Profile\Task';

    /**
     * {@inheritdoc}
     */
    protected $filterableKeys = [
        'name'       => 'string',
        'event'      => 'string',
        'running'    => 'string',
        'success'    => 'string',
        'created_at' => 'date'
    ];

    /**
     * {@inheritdoc}
     */
    public function getAllByProcessId(int $processId, array $queryParams = []) : array {
        $dbQuery = $this->query()->where('process_id', $processId);

        return $this->paginate(
            $this->filter($dbQuery, $queryParams),
            $queryParams
        );
    }
}
