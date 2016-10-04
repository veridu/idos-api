<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Validator\Profile;

use App\Validator\Traits;
use App\Validator\ValidatorInterface;

/**
 * Source Validation Rules.
 */
class Source implements ValidatorInterface {
    use Traits\AssertArray,
        Traits\AssertEntity,
        Traits\AssertId,
        Traits\AssertIpAddr,
        Traits\AssertName,
        Traits\AssertString,
        Traits\AssertOTPCode,
        Traits\ValidateFlag;
}
