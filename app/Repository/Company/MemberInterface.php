<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Repository\Company;

use App\Entity\Company\Member;
use App\Repository\RepositoryInterface;

/**
 * Member Repository Interface.
 */
interface MemberInterface extends RepositoryInterface {
    /**
     * Finds one membership by identity and company ids.
     *
     * @param int $identityId The identity identifier
     * @param int $companyId  The company identifier
     *
     * @return App\Entity\Company\Member
     */
    public function findMembership(int $identityId, int $companyId) : Member;

    /*
     * Deletes all Members based on their Company Id.
     *
     * @param int $companyId
     *
     * @return int
     */
    public function deleteByCompanyId(int $companyId) : int;

    /**
     * Find one member based on their companyId and username.
     *
     * @param int $memberId
     *
     * @return \App\Entity\Company\Member
     */
    public function findOne(int $memberId);

    /**
     * Saves a member.
     *
     * @param \App\Entity\Company\Member $member The member
     *
     * @return \App\Entity\Company\Member
     */
    public function saveOne(Member $member) : Member;
}
