<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Validator\Profile;

use App\Validator\Traits;
use App\Validator\ValidatorInterface;

/**
 * Recommendation Validation Rules.
 */
class Recommendation implements ValidatorInterface {
    use Traits\AssertId,
        Traits\AssertEntity,
        Traits\AssertType;
}
