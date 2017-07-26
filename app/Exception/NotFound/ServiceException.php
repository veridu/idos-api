<?php
/*
 * Copyright (c) 2012-2017 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

declare(strict_types = 1);

namespace App\Exception\NotFound;

use App\Exception\NotFound;

/**
 * Service not found exception.
 *
 * @apiEndpointResponse 500 schema/error.json
 */
class ServiceException extends NotFound {
}
