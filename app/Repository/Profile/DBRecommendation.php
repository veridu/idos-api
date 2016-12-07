<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository\Profile;

use App\Entity\Profile\Recommendation;
use App\Repository\AbstractSQLDBRepository;

/**
 * Database-based Recommendation Repository Implementation.
 */
class DBRecommendation extends AbstractSQLDBRepository implements RecommendationInterface {
    /**
     * The table associated with the repository.
     *
     * @var string
     */
    protected $tableName = 'recommendations';
    /**
     * The entity associated with the repository.
     *
     * @var string
     */
    protected $entityName = 'Profile\Recommendation';

    /**
     * {@inheritdoc}
     */
    public function findOne(int $userId) : Recommendation {
        return $this->findOneBy(
            [
                'user_id' => $userId
            ]
        );
    }
}
