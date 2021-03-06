<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Validator\Company;

use App\Validator\Traits;
use App\Validator\ValidatorInterface;

/**
 * Credential Validation Rules.
 */
class Credential implements ValidatorInterface {
    use Traits\AssertEntity,
        Traits\AssertId,
        Traits\AssertName,
        Traits\AssertSlug,
        Traits\ValidateFlag,
        Traits\AssertFlag;
}
