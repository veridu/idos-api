<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\NotAllowed;

use App\Exception\NotAllowed;

/**
 * CompanyProfile not allowed exception.
 *
 * @apiEndpointResponse 500 schema/error.json
 */
class CompanyProfileException extends NotAllowed {
}
