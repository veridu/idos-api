<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Score;
use App\Exception\NotFound;
use Illuminate\Support\Collection;

/**
 * Database-based Score Repository Implementation.
 */
class DBScore extends AbstractSQLDBRepository implements ScoreInterface {
    /**
     * The table associated with the repository.
     *
     * @var string
     */
    protected $tableName = 'scores';
    /**
     * The entity associated with the repository.
     *
     * @var string
     */
    protected $entityName = 'Score';
    /**
     * {@inheritdoc}
     */
    protected $filterableKeys = [
        'name'    => 'string'
    ];

    /**
     * {@inheritdoc}
     */
    public function getAllByUserIdAndAttributeName(int $userId, string $attributeName) : Collection {
        $result = $this->query()
            ->join('attributes', 'attributes.id', '=', 'scores.attribute_id')
            ->where('attributes.user_id', '=', $userId)
            ->where('attributes.name', '=', $attributeName)
            ->get(['scores.*']);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllByUserIdAttributeNameAndNames(int $userId, string $attributeName, array $queryParams = []) : Collection {
        $result = $this->query()
            ->join('attributes', 'attributes.id', '=', 'scores.attribute_id')
            ->where('attributes.user_id', '=', $userId)
            ->where('attributes.name', '=', $attributeName);

        $result = $this->filter($result, $queryParams);

        return $result->get(['scores.*']);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByAttributeId(int $attributeId) : int {
        return $this->deleteBy(['attribute_id' => $attributeId]);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByUserIdAttributeNameAndName(int $userId, string $attributeName, string $name) : Score {
        $result = new Collection();
        $result = $result->merge(
            $this->query()
                ->join('attributes', 'attributes.id', '=', 'scores.attribute_id')
                ->where('attributes.user_id', '=', $userId)
                ->where('attributes.name', '=', $attributeName)
                ->where('scores.name', '=', $name)
                ->get(['scores.*'])
        );

        if ($result->isEmpty()) {
            throw new NotFound();
        }

        return $result->first();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteOneByAttributeIdAndName(int $attributeId, string $name) : int {
        return $this->deleteBy(['attribute_id' => $attributeId, 'name' => $name]);
    }
}
