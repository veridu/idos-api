<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository\Company;

use App\Repository\RepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Hook Repository Interface.
 */
interface HookInterface extends RepositoryInterface {
    /**
     * Gets all Hooks based on their Credential Id.
     *
     * @param int $credentialId
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllByCredentialId(int $credentialId) : Collection;

    /**
     * Deletes all Hooks based on their Credential Id.
     *
     * @param int $credentialId
     *
     * @return int
     */
    public function deleteByCredentialId(int $credentialId) : int;

    /**
     * Gets all Hooks by credential pub key.
     *
     * @param string $credentialPubKey The credential pub key
     *
     * @return \Illuminate\Support\Collection A collection of \App\Entity\Company\Hook.
     */
    public function getAllByCredentialPubKey(string $credentialPubKey) : Collection;
}
