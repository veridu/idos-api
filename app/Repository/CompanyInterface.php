<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Company;
use Illuminate\Support\Collection;

/**
 * Company Repository Interface.
 */
interface CompanyInterface extends RepositoryInterface {
    /**
     * Finds a Company based on its Public Key.
     *
     * @param string $pubKey
     *
     * @throws App\Exception\NotFound
     *
     * @return App\Entity\Company
     */
    public function findByPubKey(string $pubKey) : Company;

    /**
     * Finds a Company based on its Slug.
     *
     * @param string $slug
     *
     * @throws App\Exception\NotFound
     *
     * @return App\Entity\Company
     */
    public function findBySlug(string $slug) : Company;

    /**
     * Gets all Companies based on their Parent Id.
     *
     * @param int $parentId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllByParentId(int $parentId) : Collection;

    /**
     * Deletes all Companies based on their Parent Id.
     *
     * @param int $parentId
     *
     * @return int
     */
    public function deleteByParentId(int $parentId) : int;

    /**
     * Generates a signed JWT.
     *
     * @param string $subject        The subject
     * @param string $companyPrivKey The company priv key
     * @param string $companyPubKey  The company pub key
     */
    public static function generateToken($subject, string $companyPrivKey, string $companyPubKey) : string;
}
